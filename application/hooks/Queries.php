<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Queries {
    /**
     * Instance CI.
     *
     * @param object
     */
    private $CI;

    public function __construct()
    {
        $this->CI = & get_instance();
    }

	/**
	 * Log query
	 *
	 * @return void
	 */
	public function logging()
	{
		$this->CI->load->library('Logger');
		$this->CI->logger->setLogPath('queries');
		$times = $this->CI->db->query_times;

        foreach ($this->CI->db->queries as $key => $query) { 
            if ($query) {
				$msg = "Router: ".$this->CI->router->directory."\nClass: ".$this->CI->router->class . '/' . $this->CI->router->method."\nQuery". ' => ' . trim(preg_replace('/\s+/', ' ', $query)) . ";\nExecution Time: " . $times[$key];
				$this->CI->logger->write_log('error', $msg);
            }
        } 
	}
}