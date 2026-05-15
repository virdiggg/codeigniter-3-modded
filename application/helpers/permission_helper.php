<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('getConfig')) 
{
    /**
     * Retrieve CI3 configuration item
     *
     * @param string $key
     * @return mixed
     */
    function getConfig($key) {
        $ci =& get_instance();
        return $ci->config->item($key);
    }
}

if (!function_exists('getSession')) 
{
    /**
     * Retrieve CI3 session data
     *
     * @param string $key
     * @return mixed
     */
    function getSession($key) {
        $ci =& get_instance();
        return $ci->session->userdata($key);
    }
}

if (!function_exists('hideWarnings'))
{
    /**
     * Suppress warning messages in development mode for PHP 7.4 when using phpoffice/phpspreadsheet library
     *
     * @return void
     */
    function hideWarnings()
    {
        if (ENVIRONMENT === 'development') {
            $version = explode('.', PHP_VERSION);
            if ($version[0] == 7 && $version[1] >= 3) error_reporting(E_ERROR | E_PARSE);
        }
    }
}

if (!function_exists('redirectBack')) 
{
    /**
     * Redirect to the previous page
     *
     * @return void
     */
    function redirectBack()
    {
        $ci =& get_instance();
        $ci->load->library('user_agent');
        redirect($ci->agent->referrer());
    }
}

if (!function_exists('loadHttpHeaders'))
{
    /**
     * Load standard and extra HTTP headers
     *
     * @param bool $standard Include standard headers
     * @param bool $extra Include extra security headers
     *
     * @return void
     */
    function loadHttpHeaders($standard = false, $extra = false) {
        header_remove("X-Powered-By");
        if ($standard) {
            header('Cross-Origin-Opener-Policy: same-origin', TRUE);
            header('Cross-Origin-Resource-Policy: same-origin', TRUE);
            header('Cross-Origin-Embedder-Policy: require-corp', TRUE);
            header('X-DNS-Prefetch-Control: off', TRUE);
            header('X-Download-Options: noopen', TRUE);
            header('X-Permitted-Cross-Domain-Policies: none', TRUE);
            header('X-Content-Type-Options: nosniff', TRUE);
            header('X-Frame-Options: SAMEORIGIN', TRUE);
            header('Referrer-Policy: no-referrer', TRUE);
            header('Origin-Agent-Cluster: ?1', TRUE);
            if (ENVIRONMENT === 'production') {
                header('Strict-Transport-Security: max-age=15552000; includeSubDomains', TRUE);
            }
        }
        if ($extra) {
            header("Permissions-Policy: fullscreen=(self), geolocation=(self), camera=(self)", TRUE);
            header('Clear-Site-Data: "cache"', TRUE);
        }
    }
}

if (!function_exists('ensureFolderPermissions')) {
    /**
     * Ensure folder exists and has the correct permissions
     *
     * @param string $path Folder path
     * @param int $mode Permission mode (default: 0755)
     * @param string $owner Folder owner (default: apache)
     *
     * @return void
     */
    function ensureFolderPermissions($path, $mode = DIR_WRITE_MODE, $owner = 'apache')
    {
        if (!is_dir($path)) {
            $old = umask(0);
            mkdir($path, $mode, true);
            @chown($path, $owner);
            @chgrp($path, $owner);
            umask($old);
        } else {
            $old = umask(0);
            @chmod($path, $mode);
            @chown($path, $owner);
            @chgrp($path, $owner);
            umask($old);
        }
    }
}

if (!function_exists('parseImageSlug')) {
    /**
     * Parse and validate image slug
     *
     * @param string|null $slug Encrypted slug
     *
     * @return object
     */
    function parseImageSlug($slug = null)
    {
        $ci =& get_instance();
        $defaultImage = FCPATH . 'assets' . DIRECTORY_SEPARATOR . 'no-image.jpg';
        $defaultMime = 'image/jpeg';

        if (empty($slug)) {
            return (object) ['mime' => $defaultMime, 'name' => $defaultImage];
        }

        $ci->load->helper('encrypt');
        try {
            $imagePath = decrypt($slug);
        } catch (Exception $e) {
            return (object) ['mime' => $defaultMime, 'name' => $defaultImage];
        }

        if (empty($imagePath) || !fileExists($imagePath)) {
            return (object) ['mime' => $defaultMime, 'name' => $defaultImage];
        }

        $ci->load->helper('file');
        return (object) [
            'mime' => get_mime_by_extension($imagePath),
            'name' => $imagePath,
        ];
    }
}

if (!function_exists('fileExists')) {
    /**
     * Check if a file exists and is readable
     *
     * @param string $filename File path
     *
     * @return bool
     */
    function fileExists($filename)
    {
        if (!is_readable($filename) || (!is_file($filename) && !is_dir($filename)) || !file_exists($filename)) {
            return false;
        }
        try {
            $filehandle = fopen($filename, 'r');
        } catch (Exception $e) {
            return false;
        }
        if ($filehandle === false) {
            return false;
        }
        fclose($filehandle);
        return true;
    }
}