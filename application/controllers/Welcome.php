<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Virdiggg\SeederCi3\MY_Controller;

class Welcome extends MY_Controller {
	public function index()
	{
		$this->load->helper('permission');
		return $this->withData([
			'username' => 'admin'
		])->asView('welcome_message', [
			'email' => getConfig('debug_email')
		]);
	}
}
