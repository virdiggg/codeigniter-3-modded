<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * In some cases, CodeIgniter's default error handling will display the error message instead of an error page,
 * even when the environment is set to 'production', which is not ideal for production environments.
 * This class extends the default CI_Exceptions class to display the error page without the error message.
 */
class MY_Exceptions extends CI_Exceptions
{
	/**
	 * Instance of the CodeIgniter object
	 * @var object
	 */
	protected $CI;
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * General Error Page
	 *
	 * Takes an error message as input (either as a string or an array)
	 * and displays it using the specified template.
	 *
	 * @param	string		$heading	Page heading
	 * @param	string|string[]	$message	Error message
	 * @param	string		$template	Template name
	 * @param 	int		$status_code	(default: 500)
	 *
	 * @return	string	Error page output
	 */
	public function show_error($heading, $message, $template = 'error_general', $status_code = 500)
	{
		// If we're not in development mode, we'll show the error page
		// with a generic message and status code 500
		if (ENVIRONMENT !== 'development' && !is_cli()) {
			$this->CI =& get_instance();
			$template = 'error_500';
			$message = 'Something went wrong.';
			$heading = 'Oops';
			$status_code = 500;
		}

		$output = parent::show_error($heading, $message, $template, $status_code);

		return $output;
	}
}