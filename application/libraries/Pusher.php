<?php

use Pusher\Pusher as PusherLibrary;

defined('BASEPATH') or exit('No direct script access allowed');

class Pusher
{
    private $CI;
    private $config = [];

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->config = [
            'pusher_key' => $this->CI->config->item('pusher_key'),
            'pusher_secret' => $this->CI->config->item('pusher_secret'),
            'pusher_app_id' => $this->CI->config->item('pusher_app_id'),
            'pusher_cluster' => $this->CI->config->item('pusher_cluster')
        ];
    }

    public function send(string $title = 'title', array $message = []) {
        if (empty($this->CI->config->item('pusher_key'))) {
            return;
        }

        $pusher = new PusherLibrary(
            $this->CI->config->item('pusher_key'),
            $this->CI->config->item('pusher_secret'),
            $this->CI->config->item('pusher_app_id'),
            [
                'cluster' => $this->CI->config->item('pusher_cluster'),
                'useTLS' => true
            ]
        );

        $pusher->trigger($this->CI->config->item('pusher_channel'), $title, $message);
    }
}