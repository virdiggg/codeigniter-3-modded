<?php defined('BASEPATH') or exit('No direct script access allowed');

use Virdiggg\MergeFiles\Merge as MF;
use Virdiggg\SeederCi3\MY_Controller;

class Example extends MY_Controller
{
    private $mf;

    public function __construct()
    {
        parent::__construct();
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

    public function qr_code() {
        die;
        $this->load->library('ciqrcode');

        $custom_name = 'my_qr_code.png';
        $name = 'qr_code';

        $data = 'https://www.google.com';

        $path = STORAGE_PATH;

        $confQRCode['cacheable'] = true; //boolean, the default is truede
        $confQRCode['quality'] = true; //boolean, the default is true
        $confQRCode['size'] = '1024'; //interger, the default is 1024
        $confQRCode['black'] = array(224, 255, 255); // array, default is array(255,255,255)
        $confQRCode['white'] = array(70, 130, 180); // array, default is array(0,0,0)
        $confQRCode['label'] = $data;

        $confQRCode['cachedir'] = $path . 'cache'; //string, the default is application/cache/
        $confQRCode['errorlog'] = $path . 'log'; //string, the default is application/logs/
        $confQRCode['imagedir'] = $path; // where qr code file stored

        $this->ciqrcode->initialize($confQRCode);

        $img = $custom_name ? $custom_name : $name . '_barcode.png';
        $param['savename'] = $path . $img;
        $param['data'] = $data;
        $param['level'] = 'H'; //H=High
        $param['size'] = 10;
        $this->ciqrcode->generate($param); // to generate qr code

        return $img;
    }

    public function redis() {
        die;
        $this->load->library('RedisLib');
		$this->load->helper('template');

        // Delete all redis cache
		// // $this->redislib->flush();

        // Delete specific redis cache
		$this->redislib->get(['more_apps:1321321321']);

        // Delete specific redis cache
		$this->redislib->remove(['more_apps:1321321321']);

        // Find all redis keys that match the wildcard pattern
		$this->redislib->keys('more_apps:*');

        // Select all redis cache that match the wildcard pattern
		$this->redislib->getWithWildCard('more_apps:*');

        // Delete all redis cache that match the wildcard pattern
		$this->redislib->removeWithWildCard('more_apps:*');

        // USE CASE
        // ===================================
        // Redis Cache
        $nik = '1231232131231';
        if ($this->redislib->get('more_apps:'.$nik)) {
            $apps = json_decode($this->redislib->get('more_apps:'.$nik));
        } else {
            $apps = [
                [
                    'name' => 'App 1',
                    'icon' => 'no-image.jpg',
                ],
                [
                    'name' => 'App 2',
                    'icon' => 'no-image.jpg',
                ]
            ];

            foreach ($apps as $i => &$app) {
                $apps[$i]['icon'] = !empty($apps[$i]['icon'])
                    ? assets($apps[$i]['icon'])
                    : null;
            }

            $this->redislib->set([
                'more_apps:'.$nik => json_encode($apps)
            ], 28800);
        }

        print_r($this->redislib->get('more_apps:'.$nik));
    }
}