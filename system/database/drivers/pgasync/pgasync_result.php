<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CI_DB_pgasync_result
 * -----------------------------------------------------------------------
 * Identical to the stock postgre result class — pg_get_result() returns
 * the same result resource/object type as pg_query(), so row fetching,
 * num_rows(), field metadata, etc. all work unchanged.
 * -----------------------------------------------------------------------
 */

if ( ! class_exists('CI_DB_postgre_result', FALSE))
{
	require_once BASEPATH.'database/drivers/postgre/postgre_result.php';
}

class CI_DB_pgasync_result extends CI_DB_postgre_result {

}
