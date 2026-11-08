<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['redis_scheme'] = 'tcp'; // or tls
$config['redis_host'] = 'redis'; // or 127.0.0.1
$config['redis_port'] = 6379;
$config['redis_password'] = null;
$config['redis_ca_file']  = null; // tls only