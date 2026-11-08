<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Queries {

    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    public function logging()
    {
        try {
            $this->CI->load->library('MY_Logger', NULL, 'logger');

            // Guard against method-name drift instead of fataling silently
            $this->CI->logger->setFilenamePrefix('queries');

            $times = $this->CI->db->query_times;

            foreach ($this->CI->db->queries as $key => $query) {
                if (!$query) continue;

                $msg = "Router: " . $this->CI->router->directory
                    . "\nClass: " . $this->CI->router->class . '/' . $this->CI->router->method
                    . "\nQuery => " . trim(preg_replace('/\s+/', ' ', $query)) . ";"
                    . "\nExecution Time: " . ($times[$key] ?? 'n/a');

                $this->CI->logger->write_log('error', $msg);
            }
        } catch (Throwable $e) {
            // Never fail silently again: write straight to the PHP error
            // log regardless of whether the Logger library itself works.
            error_log('[Queries hook] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        }
    }
}