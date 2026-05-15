<?php defined('BASEPATH') or exit('No direct script access allowed');

use Virdiggg\LogParserCI3\MYViewer;

class Storage extends CI_Controller
{
    public $logs;
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('permission');
        loadHttpHeaders(true);
    }

    public function index($slug = null) {
        $slug = $this->input->get('s');
        $result = parseImageSlug($slug);

        header('Content-Type: ' . $result->mime);
        header('Content-Disposition: inline; filename="' . basename($result->name) . '"');
        header('Content-Length: ' . filesize($result->name));
        readfile($result->name);
        exit;
    }

    public function logs() {
        $this->logs = new MYViewer();
        // Log path
        $this->logs->setPath(APPPATH . 'logs');
        // Log extension
        // $this->logs->setExt('php');

        $filterDate = $this->input->post('date') ? $this->input->post('date') : date('Y-m-d');

        $this->logs->setName('log-' . $filterDate);

        $result = $this->logs->getLogs();

        echo json_encode($result, JSON_PRETTY_PRINT);
        return;
    }
}