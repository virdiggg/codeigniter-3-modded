<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CodeIgniter's default profiler is a useful tool for debugging and performance optimization,
 * but they display the message right at the bottom of the page, which make it hard to read, also break the layout.
 * This class extends the default CI_Loader class to display in a modal with floating button.
 * 
 * IMPORTANT:
 * When you are customizing a print page, it's recommended to set profiler's config to false
 * so you won't see the profiler button in the page.
 */
class MY_Loader extends CI_Loader
{
    /**
    * Override the view method to add custom behavior
    *
    * @param string $view
    * @param array $vars
    * @param bool $return
    * @return mixed
    */
    public function view($view, $vars = [], $return = FALSE)
    {
        $ci =& get_instance();
        $usingBenchmark = $ci->config->item('using_benchmark');

        if ($usingBenchmark && ENVIRONMENT === 'development') {
            $ci->output->enable_profiler(TRUE);
            $sections = [
                'config'  => TRUE,
                'queries' => TRUE,
                'post' => true,
                'memory_usage' => true,
                'http_headers' => true,
                'controller_info' => true,
                'benchmarks' => true,
            ];
            $ci->output->set_profiler_sections($sections);
        }

        // Call the parent view method to actually load the view
        $output = parent::view($view, $vars, $return);

        // Return the output if requested, otherwise return void
        return $output;
    }
}