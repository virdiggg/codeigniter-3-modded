<?php defined('BASEPATH') or exit('No direct script access allowed');

use Virdiggg\SeederCi3\MY_AppController;
use Virdiggg\MergeFiles\Merge as MF;

class App extends MY_AppController
{
    private $mf;

    /**
     * Hooks for migrate() function.
     * If you want to run a callback after migrating a table,
     * ex.: create a log file after migration or run grant privileges query for a role.
     * 
     * @return bool $this
     */
    private $migrateCalled = false;
    public function __construct()
    {
        parent::__construct();
    }

    public function migrate()
    {
        parent::migrate();
        $this->migrateCalled = true;
    }

    // If you don't wish to have rollback function
    public function rollback() {
        return;
    }

    public function merge_files() {
        die;
        try {
            $this->mf = new MF();
            $this->mf->setAuthor('Me');
            $this->mf->setCreator('Me');
            $this->mf->setOutputName('merged_' . date('Ymd') . '.pdf');
            $this->mf->setOutputPath(APPPATH . 'files' . DIRECTORY_SEPARATOR);
            $this->mf->setKeywords(['pdf', 'merge', 'files']);
            $this->mf->setTitle('Title PDF');
            $this->mf->setSubject('Subject PDF');
            $this->mf->setPassword('pass');

            $files = [
                FCPATH . 'assets/no-image.jpg',
                FCPATH . 'assets/no-image.pdf',
                FCPATH . 'assets/no-image.docx',
                FCPATH . 'assets/no-image.pdf',
            ];
            $output = $this->mf->mergeToPDF($files);
            $this->load->helper('download');
            force_download($output, NULL);
        } catch (Exception $e) {
            $back = base_url();
            echo "Error: {$e->getMessage()}<script>
                setTimeout(function() {
                    window.location.href = '$back';
                }, 1500); // 1.5 detik
            </script>";
        }
    }

    public function xlsx() {
        die;
        $data[] = [
            'id', 'no_doc', 'no_inv',
        ];
        $data[] = [
            '1', 'DOC/2024/02', 'INV/2024/02',
        ];
        $data[] = [
            '2', 'DOC/2024/02', 'INV/2024/02',
        ];
        $data[] = [
            'id', 'header_id', 'product_code', 'product_name',
        ];
        $data[] = [
            '1', '1', 'CODE1','PRODUCT 1',
        ];
        $data[] = [
            '2', '1', 'CODE2','PRODUCT 2',
        ];
        $data[] = [
            '3', '2', 'CODE1','PRODUCT 1',
        ];
        $data[] = [
            '4', '1', 'CODE3','PRODUCT 3',
        ];
        $this->load->library('PhpXlsxGenerator');
        $xlsx = $this->phpxlsxgenerator->fromArray($data);
        return $xlsx->downloadAs('output_name.xlsx');
    }

    public function __destruct()
    {
        if ($this->migrateCalled) {
            // $this->db->query("GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO myrole");
            // $this->db->query("GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO myrole");
            // $this->load->library('Logger');
            // $this->logger->setLogPath('queries');
            // $this->logger->write_log('error', 'User run migration.');
        }
    }
}