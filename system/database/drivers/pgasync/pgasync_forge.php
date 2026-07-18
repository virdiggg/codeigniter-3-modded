<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CI_DB_pgasync_forge
 * -----------------------------------------------------------------------
 * No changes needed vs. the stock postgre forge — schema DDL (create
 * table, add column, etc.) has no async equivalent worth having, it's
 * reused verbatim so $this->dbforge->... keeps working.
 * -----------------------------------------------------------------------
 */

if ( ! class_exists('CI_DB_postgre_forge', FALSE))
{
	require_once BASEPATH.'database/drivers/postgre/postgre_forge.php';
}

class CI_DB_pgasync_forge extends CI_DB_postgre_forge {

}
