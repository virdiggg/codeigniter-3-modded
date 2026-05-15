<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Usage:
 * 
 * $this->load->helper('encrypt');
 * $str = 'coba doang buat encrypt';
 * $enc = encrypt($str); 656E6372797074
 * $dec = decrypt($enc); 64656372797074
 * 
 * echo $str.'<br>'.$enc.'<br>'.$dec.'<br>';
 */

if (!function_exists('encrypt')) 
{
    function parseSlug($fullPath) {
        return base_url('storage?s=' . encrypt($fullPath));
    }

    /**
     * The algorithm used for encryption.
     *
     * @param string
     */
    function secret_key()
    {
        $ci =& get_instance();

        $cipher = chiper();
        $supportedCiphers = supportedCiphers();
        $size = $supportedCiphers[strtolower($cipher)]['size'];

        // Key must be following the chosen chipher rules
        return $ci->config->item('secret_key') ?: $ci->config->item('encryption_key');
    }

    /**
     * The algorithm used for encryption.
     *
     * @param string
     */
    function chiper()
    {
        $ci =& get_instance();
        return $ci->config->item('encrypt_method') ?: 'aes-256-gcm';
    }

    /**
     * The supported cipher algorithms and their properties.
     *
     * @param array
     */
    function supportedCiphers()
    {
        return [
            'aes-128-cbc' => ['size' => 16, 'aead' => false],
            'aes-256-cbc' => ['size' => 32, 'aead' => false],
            'aes-128-gcm' => ['size' => 16, 'aead' => true],
            'aes-256-gcm' => ['size' => 32, 'aead' => true],
        ];
    }

    /**
     * Determine if the given key and cipher combination is valid.
     *
     * @param  string  $key
     * @param  string  $cipher
     * @return bool
     */
    function supported($key, $cipher)
    {
        $supportedCiphers = supportedCiphers();
        if (! isset($supportedCiphers[strtolower($cipher)])) {
            return false;
        }

        return mb_strlen($key, '8bit') === $supportedCiphers[strtolower($cipher)]['size'];
    }

    /**
     * Encrypt the given value.
     *
     * @param  mixed  $value
     * @param  bool  $serialize
     * @return string
     *
     * @throws \Exception
     */
    function encrypt($value, $serialize = true)
    {
        $cipher = chiper();
        $supportedCiphers = supportedCiphers();
        $key = secret_key();

        // if (! supported($key, $cipher)) {
        //     $ciphers = join(', ', array_keys($supportedCiphers));
        //     $size = $supportedCiphers[strtolower($cipher)]['size'];

        //     throw new \Exception("Unsupported cipher or incorrect key length. Supported ciphers are: {$ciphers}. Key must be $size character long.");
        // }

        $iv = random_bytes(openssl_cipher_iv_length(strtolower($cipher)));

        $tag = '';

        $ci =& get_instance();

        $value = $supportedCiphers[strtolower($cipher)]['aead']
            ? \openssl_encrypt(
                $serialize ? serialize($value) : $value,
                strtolower($cipher), $key, 0, $iv, $tag
            )
            : \openssl_encrypt(
                $serialize ? serialize($value) : $value,
                strtolower($cipher), $key, 0, $iv
            );

        if ($value === false) {
            throw new \Exception('Could not encrypt the data.');
        }

        $iv = base64_encode($iv);
        $tag = base64_encode($tag);

        $mac = $supportedCiphers[strtolower($cipher)]['aead']
            ? '' // For AEAD-algoritms, the tag / MAC is returned by openssl_encrypt...
            : hashed($iv, $value);

        $json = json_encode(compact('iv', 'value', 'mac', 'tag'), JSON_UNESCAPED_SLASHES);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Could not encrypt the data.');
        }

        return base64_encode($json);
    }

    /**
     * Create a MAC for the given value.
     *
     * @param  string  $iv
     * @param  mixed  $value
     * @return string
     */
    function hashed($iv, $value)
    {
        return hash_hmac('sha256', $iv.$value, secret_key());
    }

    /**
     * Decrypt the given value.
     *
     * @param  string  $payload
     * @param  bool  $unserialize
     * @return mixed
     *
     * @throws \Exception
     */
    function decrypt($payload, $unserialize = true)
    {
        $payload = getJsonPayload($payload);

        $iv = base64_decode($payload['iv']);

        ensureTagIsValid(
            $tag = empty($payload['tag']) ? null : base64_decode($payload['tag'])
        );

        // Here we will decrypt the value. If we are able to successfully decrypt it
        // we will then unserialize it and return it out to the caller. If we are
        // unable to decrypt this value we will throw out an exception message.
        $decrypted = \openssl_decrypt(
            $payload['value'], strtolower(chiper()), secret_key(), 0, $iv, $tag ?? ''
        );

        if ($decrypted === false) {
            throw new \Exception('Could not decrypt the data.');
        }

        return $unserialize ? unserialize($decrypted) : $decrypted;
    }

    /**
     * Ensure the given tag is a valid tag given the selected cipher.
     *
     * @param  string  $tag
     * @return void
     */
    function ensureTagIsValid($tag)
    {
        $supportedCiphers = supportedCiphers();
        if ($supportedCiphers[strtolower(chiper())]['aead'] && strlen($tag) !== 16) {
            throw new \Exception('Could not decrypt the data.');
        }

        if (! $supportedCiphers[strtolower(chiper())]['aead'] && is_string($tag)) {
            throw new \Exception('Unable to use tag because the cipher algorithm does not support AEAD.');
        }
    }

    /**
     * Get the JSON array from the given payload.
     *
     * @param  string  $payload
     * @return array
     *
     * @throws \Exception
     */
    function getJsonPayload($payload)
    {
        $payload = json_decode(base64_decode($payload), true);

        $supportedCiphers = supportedCiphers();

        // If the payload is not valid JSON or does not have the proper keys set we will
        // assume it is invalid and bail out of the routine since we will not be able
        // to decrypt the given value. We'll also check the MAC for this encryption.
        if (! validPayload($payload)) {
            throw new \Exception('The payload is invalid.');
        }

        if (! $supportedCiphers[strtolower(chiper())]['aead'] && ! validMac($payload)) {
            throw new \Exception('The MAC is invalid.');
        }

        return $payload;
    }

    /**
     * Determine if the MAC for the given payload is valid.
     *
     * @param  array  $payload
     * @return bool
     */
    function validMac(array $payload)
    {
        return hash_equals(
            hashed($payload['iv'], $payload['value']), $payload['mac']
        );
    }

    /**
     * Verify that the encryption payload is valid.
     *
     * @param  mixed  $payload
     * @return bool
     */
    function validPayload($payload)
    {
        return is_array($payload) && isset($payload['iv'], $payload['value'], $payload['mac']) &&
            strlen(base64_decode($payload['iv'], true)) === openssl_cipher_iv_length(strtolower(chiper()));
    }
}