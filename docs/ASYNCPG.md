# Async PostgreSQL Driver

A custom CodeIgniter 3 database driver (`pgasync`) that adds non-blocking,
concurrent PostgreSQL queries on top of the stock `postgre` driver.

---

## Overview

`pgasync` **extends** CI3's built-in `postgre` driver — it does not
replace it. Everything that already works (`$this->db->query()`, Active
Record, `$this->dbforge`, migrations) continues to work completely
unchanged.

On top of that, it adds three opt-in methods for running queries
concurrently instead of one after another:

- `query_async($sql, $binds = [])` — dispatch a query without blocking
- `await($handle, $timeout = null)` — wait for one query's result
- `poll($handle)` — non-blocking check if a result is ready
- `query_async_all($sql_list)` — convenience helper to fire + await many

### How the concurrency works

A single PostgreSQL connection can only have one query in flight at a
time — that's a protocol limitation, not a PHP one. To get real
concurrency, `pgasync` keeps a small **pool** of connections (default 4)
and round-robins queries across them. `query_async()` returns
immediately after handing the query to a pooled connection; `await()`
then sleeps efficiently (via `stream_select`, no busy-looping) until that
specific query is done.

This is most useful for **independent, parallelizable reads** — e.g.
several dashboard widgets each needing their own query, or fanning out
report queries. It does not help a request that only ever runs one query
at a time, and it is not a substitute for a true async event-loop server.

---

## Requirements

- `ext-pgsql` (already required by the stock `postgre` driver)
- PHP 7.4 – 8.3 (no PHP 8-only syntax or functions are used)

---

## Setup

In `application/config/database.php`:

```php
$db['default'] = array(
    'dsn'        => '',
    'hostname'   => 'localhost',
    'username'   => 'youruser',
    'password'   => 'yourpass',
    'database'   => 'yourdb',
    'dbdriver'   => 'pgasync',   // was 'postgre'
    'pool_size'  => 4,           // optional, default 4
    // ...leave everything else as-is
);
```

No other changes are required. Existing models/controllers keep working.

---

## Usage

### Basic concurrent queries

```php
class Dashboard_model extends CI_Model {

    public function get_widgets()
    {
        // All three are dispatched immediately and run concurrently.
        $h_sales = $this->db->query_async(
            "SELECT sum(total) AS s FROM orders WHERE created_at > now() - interval '7 days'"
        );
        $h_users = $this->db->query_async(
            "SELECT count(*) AS c FROM users WHERE active = true"
        );
        $h_top = $this->db->query_async(
            "SELECT product_id, count(*) FROM order_items GROUP BY product_id ORDER BY 2 DESC LIMIT 5"
        );

        // Each await() only waits on its own query — total wait time is
        // roughly the slowest one, not the sum of all three.
        $sales = $this->db->await($h_sales);
        $users = $this->db->await($h_users);
        $top   = $this->db->await($h_top);

        return [
            'sales' => $sales ? $sales->row() : null,
            'users' => $users ? $users->row() : null,
            'top'   => $top ? $top->result() : [],
        ];
    }
}
```

### Parameterized queries

```php
$handle = $this->db->query_async(
    "SELECT * FROM orders WHERE customer_id = $1 AND status = $2",
    [$customer_id, 'shipped']
);
$result = $this->db->await($handle);
```

### Fire-and-await-all convenience wrapper

```php
$results = $this->db->query_async_all([
    'a' => "SELECT * FROM users",
    'b' => "SELECT * FROM customers",
    'c' => "SELECT COUNT(1) FROM orders",
]);

foreach ($results as $key => $res) {
    // $res is FALSE on error, or a normal CI_DB_result-compatible object
}
```

### Non-blocking poll (advanced)

```php
$handle = $this->db->query_async("SELECT * FROM users");

// do other work...

if ($this->db->poll($handle)) {
    $result = $this->db->await($handle); // won't block, result is ready
}
```

### Releasing pool connections early

CI3 already closes everything at request shutdown, but if you want to
release pooled connections mid-request:

```php
$this->db->close_pool();
```

---

## Error handling

Failed queries return `FALSE` from `await()`, matching CI3's normal
`query()` convention. The error is also written via `log_message('error', ...)`.

```php
$result = $this->db->await($handle);

if ($result === FALSE) {
    // handle the failure
}
```

---

## Limitations

- **Transactions are not supported across async calls.** A `BEGIN` is
  meaningless here since each `query_async()` call may land on a
  different pooled connection. Keep multi-statement transactions on the
  normal synchronous `$this->db->query()` path.
- **No prepared-statement caching** — each parameterized async call
  re-sends the query text.
- **Pool sizing**: each request using async queries can open up to
  `pool_size` extra connections beyond the normal one. Size this against
  your PostgreSQL `max_connections` and expected concurrent request
  volume.

---
