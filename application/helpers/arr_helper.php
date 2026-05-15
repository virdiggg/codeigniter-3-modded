<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('sumArrValues')) {
    /**
    * Sum the values in an array per row
    *
    * @param array<array-key, T> $array
    *
    * @return array
    */
    function sumArrValues(...$array)
    {
        return array_map(function(...$arr) {
            return array_sum($arr);
        }, ...$array);
    }
}

if (!function_exists('rangeArray')) {
    /**
    * Generate an array with a range of numbers
    *
    * @param int $first First number in the range
    * @param int $last Last number in the range
    * @param int|null $interval Step interval for the range
    *
    * @return array
    */
    function rangeArray($first, $last, $interval = null)
    {
        return is_null($interval) ? range($first, $last) : range($first, $last, $interval);
    }
}

if (!function_exists('parseChildren')) {
    /**
    * Parse parent-child relationships in an array
    *
    * @param array $array Input array
    * @param string $head_key Primary key
    * @param string $parent_id_val Foreign key
    * @param string $children_val Key name for children
    *
    * @return array
    */
    function parseChildren($array, $head_key = 'id', $parent_id_val = 'parent_id', $children_val = 'childrens')
    {
        $result = [];
        if (count($array) === 0) return $result;

        $arrTemp = [];
        foreach ($array as $key => $value) {
            $value[$children_val] = [];
            $arrTemp[$value[$head_key]] = $value;
        }

        unset($array);
        $array = $arrTemp;
        unset($arrTemp);

        $tree = [];
        foreach($array as &$value) {
            if ($parent = isset($value[$parent_id_val]) ? $value[$parent_id_val] : NULL) {
                $array[$parent][$children_val][] = &$value;
            } else {
                $tree[] = &$value;
            }
        }

        unset($value);
        $array = $tree;
        unset($tree);

        $result = [];
        for($j = 0; $j < count($array); $j++) {
            if (isset($array[$j][$children_val]) && !isset($array[$j][$head_key])) {
                $result = array_merge($result, $array[$j][$children_val]);
            } else {
                $result[] = $array[$j];
            }
        }

        array_multisort(array_column($result, $parent_id_val), array_column($result, $head_key), SORT_ASC, $result);
        return $result;
    }
}

if (!function_exists('arrSort')) {
    /**
    * Sort an array by a specific column
    *
    * @param array $arr Input array
    * @param string $column Column to sort by
    * @param int $direction Sorting direction
    *
    * @return array
    */
    function arrSort(&$arr, $column = '', $direction = SORT_ASC)
    {
        if (!arrIsMultidimensional($arr)) {
            $direction === SORT_ASC ? sort($arr, SORT_STRING) : rsort($arr, SORT_STRING);
            return $arr;
        }
        array_multisort(array_column($arr, $column), $direction, $arr);
        return $arr;
    }
}

if (!function_exists('arrIsMultidimensional')) {
    /**
    * Check if an array is multidimensional
    *
    * @param array $array Input array
    *
    * @return bool
    */
    function arrIsMultidimensional(&$array)
    {
        rsort($array);
        return isset($array[0]) && is_array($array[0]);
    }
}

if (!function_exists('flatten')) {
    /**
    * Flatten a multidimensional array
    *
    * @param array $arr Input array
    *
    * @return array
    */
    function flatten($arr)
    {
        $result = [];
        array_walk_recursive($arr, function($a) use (&$result) {
            $result[] = $a;
        });
        return array_values(array_filter(array_unique($result), 'strlen'));
    }
}

if (!function_exists('arr_unique_multidimensional')) {
    /**
    * Remove duplicate elements from a multidimensional array
    *
    * @param array $input Input array
    *
    * @return array
    */
    function arr_unique_multidimensional($input)
    {
        return array_values(array_intersect_key($input, array_unique(array_map('serialize', $input))));
    }
}

if (!function_exists('keyBy')) {
    /**
    * Convert an array into an associative array indexed by a specified key
    *
    * @param array $array Input array
    * @param string|array $arrayKeys Key(s) to use as index
    * @param bool $isArray Whether to store values as arrays
    * @param string $separator Separator for composite keys
    *
    * @return array
    */
    function keyBy($array, $arrayKeys, $isArray = false, $separator = '|')
    {
        $result = [];
        if (empty($array)) return $result;

        // Ensure array is properly formatted
        $array = json_decode(json_encode($array), true);

        foreach ($array as $ar) {
            $key = join($separator, array_map(function ($k) use ($ar) {
                return $ar[$k];
            }, (array) $arrayKeys));
            $isArray ? $result[$key][] = $ar : $result[$key] = $ar;
        }

        return $result;
    }
}
