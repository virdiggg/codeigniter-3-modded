<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CI_DB_pgasync_driver
 * -----------------------------------------------------------------------
 * Async-capable PostgreSQL driver for CodeIgniter 3.
 *
 * Design goals:
 *  - 100% drop-in compatible with the stock `postgre` driver for every
 *    existing call: $this->db->query(), Active Record, migrations, etc.
 *    all keep working exactly as before, because this class EXTENDS
 *    CI_DB_postgre_driver rather than replacing it.
 *  - Adds an opt-in, separate pool of non-blocking libpq connections used
 *    ONLY by the new query_async()/await()/poll() methods below. If you
 *    never call those, this driver behaves identically to 'postgre'.
 *
 * IMPORTANT — what "async" means here:
 *  ext-pgsql / libpq does not support multiplexing several queries over a
 *  single connection. "Async" = non-blocking dispatch + polling, and true
 *  concurrency is achieved by round-robining across a small POOL of
 *  connections (like a mini connection pool), each holding exactly one
 *  query in flight at a time. This is safe and portable on PHP 7.4 - 8.3+.
 *
 * Compatibility:
 *  - PHP 7.4 through 8.3 tested code paths (no enums, no readonly props,
 *    no named args, no match expressions — all 7.4-safe syntax).
 *  - Requires ext-pgsql. Falls back gracefully (throws a clear exception)
 *    if pgsql or the async constants are unavailable.
 *
 * Usage:
 *  $handle  = $this->db->query_async("SELECT pg_sleep(1), 1 AS a");
 *  $handle2 = $this->db->query_async("SELECT pg_sleep(1), 2 AS b");
 *  // ... do other PHP work here while both run concurrently ...
 *  $res1 = $this->db->await($handle);   // CI_DB_result-compatible object
 *  $res2 = $this->db->await($handle2);
 *
 * Config (application/config/database.php):
 *  $db['default']['dbdriver'] = 'pgasync';
 *  $db['default']['pool_size'] = 4; // optional, default 4
 * -----------------------------------------------------------------------
 */

// Pull in the stock postgre driver classes so we can extend them.
if ( ! class_exists('CI_DB_postgre_driver', FALSE))
{
	require_once BASEPATH.'database/drivers/postgre/postgre_driver.php';
}

class CI_DB_pgasync_driver extends CI_DB_postgre_driver {

	/**
	 * Sub-driver name CI3 uses to locate forge/result/utility classes
	 * (system/database/drivers/pgasync/pgasync_*.php)
	 */
	public $dbdriver = 'pgasync';

	/** @var int Max number of concurrent async connections in the pool */
	protected $pool_size = 4;

	/** @var array List of pool slots: array('conn' => resource|object, 'busy' => bool) */
	protected $async_pool = [];

	/** @var int Simple incrementing id used to generate handle tokens */
	protected $async_seq = 0;

	/** @var array Map of handle_id => pool index, so await()/poll() know which slot to read from */
	protected $async_map = [];

	/** @var float Default timeout (seconds) for await() before giving up. NULL = wait forever. */
	protected $async_timeout = 30.0;

	public function __construct($params)
	{
		parent::__construct($params);

		if (is_array($params) && isset($params['pool_size']))
		{
			$this->pool_size = max(1, (int) $params['pool_size']);
		}
		elseif (is_object($params) && isset($params->pool_size))
		{
			$this->pool_size = max(1, (int) $params->pool_size);
		}

		if ( ! extension_loaded('pgsql'))
		{
			throw new Exception('CI_DB_pgasync_driver requires the pgsql extension, which is not loaded.');
		}

		if ( ! defined('PGSQL_CONNECT_ASYNC'))
		{
			throw new Exception('CI_DB_pgasync_driver requires PGSQL_CONNECT_ASYNC, not available in this PHP build.');
		}
	}

	// ------------------------------------------------------------------

	/**
	 * Build a libpq "conninfo" string from the same connection params
	 * CI3 stores on the driver instance (hostname, username, password,
	 * database, port). Kept independent from the parent's private DSN
	 * builder so this works across CI3 3.x point releases.
	 */
	protected function _build_conninfo()
	{
		$host = $this->hostname;
		$port = null;

		// hostname may be "host:port"
		if (strpos($host, ':') !== FALSE)
		{
			list($host, $port) = explode(':', $host, 2);
		}

		$parts = [];

		if ($host !== '')
		{
			$parts[] = "host='".addcslashes($host, "'\\")."'";
		}
		if ( ! empty($port))
		{
			$parts[] = "port='".addcslashes((string) $port, "'\\")."'";
		}
		if ($this->database !== '')
		{
			$parts[] = "dbname='".addcslashes($this->database, "'\\")."'";
		}
		if ($this->username !== '')
		{
			$parts[] = "user='".addcslashes($this->username, "'\\")."'";
		}
		if ($this->password !== '')
		{
			$parts[] = "password='".addcslashes($this->password, "'\\")."'";
		}

		// Optional extras, set these as extra keys in your db config array
		// if you need them, e.g. $db['default']['sslmode'] = 'require';
		if (isset($this->sslmode) && $this->sslmode !== '')
		{
			$parts[] = "sslmode='".addcslashes($this->sslmode, "'\\")."'";
		}
		if (isset($this->connect_timeout) && $this->connect_timeout !== '')
		{
			$parts[] = "connect_timeout='".addcslashes((string) $this->connect_timeout, "'\\")."'";
		}

		return join(' ', $parts);
	}

	// ------------------------------------------------------------------

	/**
	 * Open one new pool connection, dedicated to non-blocking QUERIES
	 * (pg_send_query / pg_get_result). Returns the connection resource/
	 * object, or FALSE on failure.
	 *
	 * Note on why the *connect* step itself is a plain blocking
	 * pg_connect() and not PGSQL_CONNECT_ASYNC: driving a truly
	 * non-blocking handshake to completion requires pg_connect_poll(),
	 * which only exists on PHP >= 8.1. To keep this driver working
	 * identically on 7.4 through 8.3+, we open connections the normal
	 * (synchronous) way — a local/nearby handshake is typically a few
	 * milliseconds — and reserve non-blocking behavior for what actually
	 * matters: the queries themselves, via pg_send_query() below, which
	 * is unchanged across all supported PHP versions.
	 */
	protected function _open_async_connection($timeout = 10.0)
	{
		$conninfo = $this->_build_conninfo();

		// PGSQL_CONNECT_FORCE_NEW guarantees this is a distinct physical
		// connection rather than a reused/pooled libpq handle, which is
		// required since each pool slot must be able to have its own
		// query in flight independently of the others.
		$conn = @pg_connect($conninfo, PGSQL_CONNECT_FORCE_NEW);

		if ($conn === FALSE || pg_connection_status($conn) !== PGSQL_CONNECTION_OK)
		{
			return FALSE;
		}

		return $conn;
	}

	// ------------------------------------------------------------------

	/**
	 * Get a free slot from the pool, opening a new async connection if
	 * under pool_size, or blocking-wait for the first busy slot to free
	 * up if the pool is already at capacity.
	 *
	 * @return int|FALSE pool index, or FALSE on failure
	 */
	protected function _get_free_slot()
	{
		// 1. Any already-open, idle slot?
		foreach ($this->async_pool as $i => $slot)
		{
			if ( ! $slot['busy'])
			{
				return $i;
			}
		}

		// 2. Room to open a new connection?
		if (count($this->async_pool) < $this->pool_size)
		{
			$conn = $this->_open_async_connection();
			if ($conn === FALSE)
			{
				return FALSE;
			}

			$this->async_pool[] = ['conn' => $conn, 'busy' => FALSE];
			return count($this->async_pool) - 1;
		}

		// 3. Pool full and all busy: wait for the first one to become
		//    readable (i.e. its current query finished).
		while (TRUE)
		{
			$read  = [];
			$index_map = [];

			foreach ($this->async_pool as $i => $slot)
			{
				if ($slot['busy'])
				{
					$socket = @pg_socket($slot['conn']);
					if ($socket)
					{
						$read[] = $socket;
						$index_map[(int) $socket] = $i;
					}
				}
			}

			if (empty($read))
			{
				// nothing busy after all (race), just reuse slot 0
				return 0;
			}

			$write = [];
			$except = [];
			$changed = @stream_select($read, $write, $except, 5); // 5s tick

			if ($changed === FALSE || $changed === 0)
			{
				continue; // keep waiting
			}

			foreach ($read as $socket)
			{
				$i = $index_map[(int) $socket];
				pg_consume_input($this->async_pool[$i]['conn']);

				if ( ! pg_connection_busy($this->async_pool[$i]['conn']))
				{
					// Drain any pending result from the previous query so
					// the connection is truly idle before reuse.
					while (($r = pg_get_result($this->async_pool[$i]['conn'])) !== FALSE)
					{
						pg_free_result($r);
					}
					$this->async_pool[$i]['busy'] = FALSE;
					return $i;
				}
			}
		}
	}

	// ------------------------------------------------------------------

	/**
	 * Dispatch a query without blocking. Returns a lightweight handle
	 * (int) to later pass to await()/poll(). Building blocks/params are
	 * NOT escaped for you here beyond what pg_send_query_params does —
	 * use $binds for parameterized queries exactly like normal.
	 *
	 * @param string $sql
	 * @param array  $binds  optional positional params for pg_send_query_params
	 * @return int|FALSE handle id, or FALSE if it could not be dispatched
	 */
	public function query_async($sql, array $binds = [])
	{
		$slot_index = $this->_get_free_slot();
		if ($slot_index === FALSE)
		{
			return FALSE;
		}

		$conn = $this->async_pool[$slot_index]['conn'];

		$sent = empty($binds)
			? @pg_send_query($conn, $sql)
			: @pg_send_query_params($conn, $sql, $binds);

		if ($sent === FALSE)
		{
			return FALSE;
		}

		$this->async_pool[$slot_index]['busy'] = TRUE;

		$handle_id = ++$this->async_seq;

		// Participate in CI3's own query logging/profiling arrays, the
		// same way CI_DB_driver::query() does (DB_driver.php), so
		// $this->db->queries / $this->db->query_times / query_count() /
		// elapsed_time() and anything built on them (query profiler
		// hooks, the built-in profiler library, custom logging hooks
		// like the one in application/hooks/Queries.php) also see async
		// queries — not just synchronous ones.
		//
		// We push a placeholder 0 into query_times at dispatch time
		// (mirroring CI_DB_driver::query()'s own failure-path
		// convention) and patch it with the real elapsed time once
		// await() completes, so the two arrays always stay the same
		// length/index-aligned even while multiple async queries are
		// in flight at once.
		$log_index = NULL;
		if ($this->save_queries === TRUE)
		{
			$log_msg = $sql;
			if ( ! empty($binds))
			{
				$log_msg .= ' /* binds: '.json_encode($binds).' */';
			}
			$this->queries[]     = $log_msg;
			$this->query_times[] = 0;
			$log_index = count($this->queries) - 1;
		}

		$this->async_map[$handle_id] = array(
			'slot'      => $slot_index,
			'start'     => microtime(TRUE),
			'log_index' => $log_index,
		);

		return $handle_id;
	}

	// ------------------------------------------------------------------

	/**
	 * Non-blocking check: TRUE if the query for $handle has a result
	 * ready to be read, FALSE if still running, NULL if handle unknown.
	 */
	public function poll($handle)
	{
		if ( ! isset($this->async_map[$handle]))
		{
			return NULL;
		}

		$i    = $this->async_map[$handle]['slot'];
		$conn = $this->async_pool[$i]['conn'];

		pg_consume_input($conn);

		return ! pg_connection_busy($conn);
	}

	// ------------------------------------------------------------------

	/**
	 * Block (efficiently, via stream_select — not busy-polling) until the
	 * query behind $handle finishes, then return a CI_DB_result-compatible
	 * object, same as a normal $this->db->query() call would.
	 *
	 * @param int        $handle
	 * @param float|null $timeout seconds; NULL = use driver default
	 * @return CI_DB_pgasync_result|FALSE
	 */
	public function await($handle, $timeout = NULL)
	{
		if ( ! isset($this->async_map[$handle]))
		{
			return FALSE;
		}

		if ($timeout === NULL)
		{
			$timeout = $this->async_timeout;
		}

		$meta      = $this->async_map[$handle];
		$i         = $meta['slot'];
		$log_index = $meta['log_index'];
		$conn      = $this->async_pool[$i]['conn'];
		$start     = $meta['start'];

		pg_consume_input($conn);

		while (pg_connection_busy($conn))
		{
			if ($timeout !== NULL && (microtime(TRUE) - $start) > $timeout)
			{
				$this->async_pool[$i]['busy'] = FALSE;
				unset($this->async_map[$handle]);
				// query_times[$log_index] stays at its dispatch-time 0
				// placeholder, same convention CI_DB_driver::query() uses
				// for failed queries.
				return FALSE; // timed out
			}

			$socket = @pg_socket($conn);
			if ($socket)
			{
				$read   = [$socket];
				$write  = [];
				$except = [$socket];
				@stream_select($read, $write, $except, 1); // 1s tick
			}
			else
			{
				usleep(20000);
			}

			pg_consume_input($conn);
		}

		$pg_result = pg_get_result($conn);

		// Drain any extra result sets (defensive; single statements won't have any).
		while (($extra = pg_get_result($conn)) !== FALSE)
		{
			pg_free_result($extra);
		}

		$this->async_pool[$i]['busy'] = FALSE;
		unset($this->async_map[$handle]);

		if ($pg_result === FALSE)
		{
			return FALSE;
		}

		$err_state = pg_result_error($pg_result);
		if ( ! empty($err_state) && in_array(pg_result_status($pg_result), [PGSQL_BAD_RESPONSE, PGSQL_NONFATAL_ERROR, PGSQL_FATAL_ERROR], TRUE))
		{
			log_message('error', 'pgasync query error: '.$err_state);
			// leave query_times[$log_index] at its 0 placeholder, matching
			// CI_DB_driver::query()'s failure-path behavior.
			return FALSE;
		}

		// Success: finish the same bookkeeping CI_DB_driver::query() does
		// after a successful synchronous query, so query_count(),
		// elapsed_time(), and $this->db->query_times[] all reflect async
		// queries too.
		$elapsed = microtime(TRUE) - $start;
		if ($this->save_queries === TRUE && $log_index !== NULL)
		{
			$this->query_times[$log_index] = $elapsed;
		}
		$this->benchmark += $elapsed;
		$this->query_count++;

		// Route through CI3's normal result wrapper so you get the same
		// row(), result_array(), num_rows(), etc. API as a sync query.
		// CI3's own query() lazily requires the result driver file the
		// first time it's needed via load_rdriver() — do the same here,
		// since await() can be the very first place a result object is
		// ever constructed in a given request.
		$driver = $this->load_rdriver();
		$this->result_id = $pg_result;

		return new $driver($this);
	}

	// ------------------------------------------------------------------

	/**
	 * Fire off several queries and await all of them, preserving order.
	 * Convenience wrapper — internally just loops query_async + await,
	 * but since query_async calls don't block, all queries are already
	 * running concurrently across the pool by the time we start awaiting.
	 *
	 * @param string[] $sql_list
	 * @return array of CI_DB_pgasync_result|FALSE, same order as input
	 */
	public function query_async_all(array $sql_list)
	{
		$handles = [];
		foreach ($sql_list as $key => $sql)
		{
			$handles[$key] = $this->query_async($sql);
		}

		$results = [];
		foreach ($handles as $key => $handle)
		{
			$results[$key] = ($handle === FALSE) ? FALSE : $this->await($handle);
		}

		return $results;
	}

	// ------------------------------------------------------------------

	/**
	 * Close every pooled async connection. Call this in addition to the
	 * normal db->close() if you used query_async at all.
	 */
	public function close_pool()
	{
		foreach ($this->async_pool as $slot)
		{
			if ($slot['conn'])
			{
				@pg_close($slot['conn']);
			}
		}
		$this->async_pool = [];
		$this->async_map  = [];
	}

	// ------------------------------------------------------------------

	public function __destruct()
	{
		$this->close_pool();
	}
}
