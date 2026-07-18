<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CI_DB_pgasync_utility
 * -----------------------------------------------------------------------
 * Reused verbatim from the stock postgre utility class (db backup /
 * optimize helpers have no async equivalent).
 * -----------------------------------------------------------------------
 */

if ( ! class_exists('CI_DB_postgre_utility', FALSE))
{
	require_once BASEPATH.'database/drivers/postgre/postgre_utility.php';
}

class CI_DB_pgasync_utility extends CI_DB_postgre_utility {

}
