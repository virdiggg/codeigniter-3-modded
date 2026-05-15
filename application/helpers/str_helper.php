<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('numberToWords'))
{
    /**
     * Convert number to words in Indonesian currency format.
     *
     * @param int $number
     * @return string
     */
    function numberToWords($number)
    {
        $words = trim(convertToWords(normalizeNumber($number)));

        $prefix = '';
        if ($number < 0) $prefix = 'minus ';

        return ucwords($prefix . $words . ' rupiah');
    }
}

if (!function_exists('convertToWords'))
{
    /**
     * Convert number to Indonesian words.
     *
     * @param int $number
     * @return string
     */
    function convertToWords($number)
    {
        $value = abs($number);
        $words = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];
        $result = "";

        if ($value < 12) {
            $result = " " . $words[$value];
        } else if ($value < 20) {
            $result = convertToWords($value - 10) . " belas";
        } else if ($value < 100) {
            $result = convertToWords($value / 10) . " puluh" . convertToWords($value % 10);
        } else if ($value < 200) {
            $result = " seratus" . convertToWords($value - 100);
        } else if ($value < 1000) {
            $result = convertToWords($value / 100) . " ratus" . convertToWords($value % 100);
        } else if ($value < 2000) {
            $result = " seribu" . convertToWords($value - 1000);
        } else if ($value < 1000000) {
            $result = convertToWords($value / 1000) . " ribu" . convertToWords($value % 1000);
        } else if ($value < 1000000000) {
            $result = convertToWords($value / 1000000) . " juta" . convertToWords($value % 1000000);
        } else if ($value < 1000000000000) {
            $result = convertToWords($value / 1000000000) . " milyar" . convertToWords(fmod($value, 1000000000));
        } else if ($value < 1000000000000000) {
            $result = convertToWords($value / 1000000000000) . " trilyun" . convertToWords(fmod($value, 1000000000000));
        }

        return $result;
    }
}

if (!function_exists('convertBytes'))
{
    /**
     * Convert bytes to human-readable format.
     *
     * @param int|float $size
     * @return string
     */
    function convertBytes($size) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $units[$i];
    }
}

if (!function_exists('formatCurrency')) {
    /**
     * Format number into Indonesian Rupiah format.
     *
     * @param int|string $amount
     * @return string Rp. 9.000.000
     */
    function formatCurrency($amount)
    {
        return 'Rp. ' . formatThousand($amount);
    }
}

if (!function_exists('formatThousand')) {
    /**
     * Format number with thousand separator.
     *
     * @param int|string $amount
     * @return string 9.000.000
     */
    function formatThousand($amount)
    {
        return number_format($amount, 0, ',', '.');
    }
}

if (!function_exists('generateRandomHex')) {
    /**
     * Generate a random Hex color.
     *
     * @return string
     */
    function generateRandomHex()
    {
        return '#' . strtoupper(str_pad(dechex(rand(0x000000, 0xFFFFFF)), 6, 0, STR_PAD_LEFT));
    }
}

if (!function_exists('getMicroTimeDateTime')) {
    /**
     * Get the current date-time with microseconds.
     *
     * @return string
     */
    function getMicroTimeDateTime()
    {
        return date('Y-m-d H:i:s.', time()) . gettimeofday()['usec'];
    }
}

if (!function_exists('isValidJson')) {
    /**
     * Check if a given string is a valid JSON format.
     *
     * @param string $value
     * @return bool
     */
    function isValidJson($value)
    {
        if (!is_string($value)) {
            return false;
        }

        try {
            json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}

if (!function_exists('formatJson')) {
    /**
     * Format JSON for better readability with syntax highlighting.
     *
     * @param string $json
     * @return string
     */
    function formatJson($json) {
        $data = json_decode($json, true);
        if ($data === null) {
            return '<pre>Error: Invalid JSON string</pre>';
        }
        return '<pre style="font-family: monospace; white-space: pre-wrap; background-color: #000; padding: 10px">' . processJson($data) . '</pre>';
    }
}

if (!function_exists('processJson')) {
    /**
     * Recursively process JSON data into a formatted string.
     *
     * @param array $data
     * @param int $indent
     * @return string
     */
    function processJson($data, $indent = 0) {
        $output = '<span style="color: #FF8400;">';
        $indentation = str_repeat('  ', $indent);

        if (is_array($data)) {
            $output .= "{\n";
            $indent++;
            foreach ($data as $key => $value) {
                $output .= $indentation . '  <span style="color: #FF4444;">"' . htmlentities($key) . '"</span>: ';
                $output .= is_array($value) ? processJson($value, $indent) : '<span style="color: #9ED839;">"' . htmlentities($value) . '"</span>';
                $output .= ",\n";
            }
            $output = rtrim($output, ",\n") . "\n";
            $output .= $indentation . '}';
        }
        return $output . '</span>';
    }
}

if (!function_exists('normalizeNumber')) {
    /**
     * Remove all characters except numbers.
     *
     * @param string $input
     * @return string
     */
    function normalizeNumber($input)
    {
        return preg_replace('/[^0-9]++/', '', $input);
    }
}

if (!function_exists('formatPhoneToID')) {
    /**
     * Format phone number to Indonesian country code (62).
     *
     * @param string $phone
     * @return string "08123456789" => "628123456789"
     */
    function formatPhoneToID($phone)
    {
        $phone = normalizeNumber($phone);
        if (startsWith($phone, '0')) {
            $phone = after($phone, '0');
        } elseif (startsWith($phone, '62')) {
            $phone = after($phone, '62');
        }
        return '62' . $phone;
    }
}

if (!function_exists('formatPhoneWithZero')) {
    /**
     * Format phone number to start with zero.
     *
     * @param string $phone
     * @return string "08123456789" => "08123456789"
     */
    function formatPhoneWithZero($phone)
    {
        $phone = normalizeNumber($phone);
        if (startsWith($phone, '0')) {
            $phone = after($phone, '0');
        } elseif (startsWith($phone, '62')) {
            $phone = after($phone, '62');
        }
        return '0' . $phone;
    }
}

if (!function_exists('sanitizeString')) {
    /**
     * Trim whitespace, remove PHP/HTML tags, strip Unicode NO-BREAK SPACE (U+00A0),
     * and convert special characters to normal characters.
     *
     * @param string $string
     * @return string
     */
    function sanitizeString($string)
    {
        return htmlspecialchars(strip_tags(trim(preg_replace('/\xc2\xa0/', '', $string))));
    }
}

if (!function_exists('formatDateID')) {
    /**
     * Format date to Indonesian format (dd Month yyyy).
     *
     * @param string $date Format yyyy-mm-dd
     * @return string
     */
    function formatDateID($date)
    {
        list($year, $month, $day) = explode('-', $date);
        return $day . ' ' . getMonthName($month) . ' ' . $year;
    }
}

if (!function_exists('formatDateTimeID')) {
    /**
     * Format datetime to Indonesian format (dd Month yyyy hh:mm).
     *
     * @param string $datetime Format yyyy-mm-dd hh:mm:ss
     * @return string
     */
    function formatDateTimeID($datetime)
    {
        list($date, $time) = explode(' ', $datetime);
        list($year, $month, $day) = explode('-', $date);
        return $day . ' ' . getMonthName($month) . ' ' . $year . ' ' . date('H:i', strtotime($time));
    }
}

if (!function_exists('getMonthName')) {
    /**
     * Get the name of the month in Indonesian.
     *
     * @param int|string $month
     * @return string
     */
    function getMonthName($month)
    {
        $months = getMonthArray();
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        return isset($months[$month]) ? $months[$month] : '';
    }
}

if (!function_exists('getMonthArray')) {
    /**
     * Array of months in Indonesian.
     *
     * @param string $type
     * @return array
     */
    function getMonthArray($type = '2d')
    {
        return $type === '2d' ?
        [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ] : [
            ['index' => '01', 'name' => 'Januari'], ['index' => '02', 'name' => 'Februari'],
            ['index' => '03', 'name' => 'Maret'], ['index' => '04', 'name' => 'April'],
            ['index' => '05', 'name' => 'Mei'], ['index' => '06', 'name' => 'Juni'],
            ['index' => '07', 'name' => 'Juli'], ['index' => '08', 'name' => 'Agustus'],
            ['index' => '09', 'name' => 'September'], ['index' => '10', 'name' => 'Oktober'],
            ['index' => '11', 'name' => 'November'], ['index' => '12', 'name' => 'Desember'],
        ];
    }
}

if (!function_exists('startsWith')) {
    /**
    * Determine if a given string starts with a given substring. Case sensitive.
    *
    * @param  string  $haystack
    * @param  string|string[]  $needles
    * @return bool
    */
    function startsWith($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ((string) $needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('after')) {
    /**
    * Return the remainder of a string after the first occurrence of a given value.
    *
    * @param  string  $subject
    * @param  string  $search
    * @return string
    */
    function after($subject, $search)
    {
        return $search === '' ? $subject : array_reverse(explode($search, $subject, 2))[0];
    }
}

if (!function_exists('before')) {
    /**
    * Get the portion of a string before the first occurrence of a given value.
    *
    * @param  string  $subject
    * @param  string  $search
    * @return string
    */
    function before($subject, $search)
    {
        if ($search === '') {
            return $subject;
        }

        $result = strstr($subject, (string) $search, true);

        return $result === false ? $subject : $result;
    }
}

if (!function_exists('beforeLast')) {
    /**
    * Get the portion of a string before the last occurrence of a given value.
    *
    * @param  string  $subject
    * @param  string  $search
    * @return string
    */
    function beforeLast($subject, $search)
    {
        if ($search === '') {
            return $subject;
        }

        $pos = mb_strrpos($subject, $search);

        if ($pos === false) {
            return $subject;
        }

        return substr($subject, 0, $pos);
    }
}

if (!function_exists('isMenuActive')) {
    /**
    * Determine if the said endpoint is active
    *
    * @param string $endpoint
    * @return string
    */
    function isMenuActive($endpoint) {
        return isActive($endpoint) ? 'active' : '';
    }
}

if (!function_exists('isActive')) {
    /**
    * Determine if the said endpoint is active
    *
    * @param string $endpoint
    * @return bool
    */
    function isActive($endpoint) {
        $current_path = parse_url(current_url(), PHP_URL_PATH);
        $endpoint_path = parse_url($endpoint, PHP_URL_PATH);

        $res = strpos($current_path, $endpoint_path);
        return is_int($res) ? true : false;
    }
}

if (!function_exists('buildQueryString')) {
    /**
     * Convert an associative array into a query string.
     *
     * @param array $data Input data
     * @param bool $urlencode Whether to URL encode the values
     * @return string|null
     */
    function buildQueryString($data, $urlencode = false)
    {
        if (empty($data)) return null;

        $result = [];
        foreach ($data as $key => $value) {
            $formattedValue = $urlencode ? urlencode($value) : $value;
            $result[] = $key . '=' . $formattedValue;
        }

        return join('&', $result);
    }
}

if (!function_exists('removeSpecialChars')) {
    /**
     * Remove special characters, keeping only alphanumeric characters and whitespace.
     *
     * @param string $input
     * @return string
     */
    function removeSpecialChars($input)
    {
        return preg_replace("/[^a-zA-Z0-9\s]/", ' ', $input);
    }
}