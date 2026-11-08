<?php
defined('BASEPATH') or exit('No direct script access allowed');

use \Predis\Client as PredisClient;

class RedisLib
{
    private $CI;
    private $client;

    const TIMEOUT = 3600; // 1 hour

    public function __construct()
    {
        $this->CI = & get_instance();
        $this->CI->load->config('redis');

        $scheme = $this->CI->config->item('redis_scheme') ?: 'tcp';

        $options = [
            'scheme' => $scheme,
            'host' => $this->CI->config->item('redis_host') ?: '127.0.0.1',
            'port' => $this->CI->config->item('redis_port') ?: 6379,
            'timeout' => 5,
            'read_write_timeout' => 5,
        ];

        // Authentication
        $password = $this->CI->config->item('redis_password');

        if (!empty($password)) {
            $options['password'] = $password;
        }

        // TLS configuration
        if ($scheme === 'tls') {
            $options['ssl'] = [
                'cafile' => $this->CI->config->item('redis_ca_file'),
                'verify_peer' => true,
                'verify_peer_name' => true,
            ];
        }

        $this->client = new PredisClient($options);
    }

    public function test()
    {
        try {
            return $this->client->ping();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function get($key = null)
    {
        if (empty($key)) {
            return null;
        }

        try {
            return $this->client->get($key);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function set(array $param = [], int $timeout = self::TIMEOUT)
    {
        if (empty($param)) {
            return false;
        }

        try {
            foreach ($param as $key => $value) {
                if ($timeout !== null) {
                    $this->client->setex($key, $timeout, $value);
                } else {
                    $this->client->set($key, $value);
                }
            }

            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function removeWithWildCard($key = null)
    {
        if (empty($key)) {
            return false;
        }

        try {
            $cursor = '0';

            do {
                $response = $this->client->scan($cursor, [
                    'match' => $key,
                    'count' => 100,
                ]);

                $cursor = $response[0];
                $keys = $response[1];

                if (!empty($keys)) {
                    $this->remove($keys);
                }
            } while ((string) $cursor !== '0');

            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function remove(array $key = [])
    {
        if (empty((array) $key)) {
            return null;
        }

        try {
            $this->client->del((array) $key);
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function flush()
    {
        try {
            $this->client->flushall();
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
