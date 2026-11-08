<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Virdiggg\SeederCi3\MY_Controller;

class Dashboard extends MY_Controller {
	public function index()
	{
		$this->load->helper(['permission', 'template']);
        return $this->withData([
            'username' => 'admin'
        ])->asTemplate('dashboard', [
            'email' => getConfig('debug_email')
        ]);
	}
}
