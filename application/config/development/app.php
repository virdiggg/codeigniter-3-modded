<?php defined('BASEPATH') or exit('No direct script access allowed');

// Default timezone GMT +7
date_default_timezone_set('Asia/Jakarta');

/**
 * We don't want to add our custom config in config.php, so we use this file.
 * 
 * You can use this custom config to store your development and production config separately
 * because this file is ignored by git in application/config/production. This config is autoloaded.
 */

/**
 * In case you want to have development handler for email or whatsapp recipient, you can use this
 */
$config['debug_email'] = 'test@example.com';
$config['debug_phone'] = '082121212121';

/**
 * Encryption key, using helper encrypt
 * 
 * Secret Key is required for encrypt and decrypt, and must be unique.
 * You can't decrypt if you don't know the secret key or using different secret key.
 * 
 * Encryption method is its encryption algorithm, default to 'aes-256-gcm'.
 * You can use any of the following:
 *  'aes-128-cbc'
 *  'aes-256-cbc'
 *  'aes-128-gcm'
 *  'aes-256-gcm'
 */
$config['secret_key']       = 'K8346TPR4CWGCA5F3ZS2KZLZPJ4XCP59';
$config['encrypt_method']   = 'aes-256-gcm';

/**
 * Determine whether to use the profiler or not
 * 
 * If using_benchmark is true, and project is in production, the profiler will not be shown
 * If using_benchmark is true, and project is in development, the profiler will be shown
 * If using_benchmark is false, the profiler will not be shown regardless if project is in production or development
*/
$config['using_benchmark']         = false; // true or false
