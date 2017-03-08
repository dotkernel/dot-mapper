<?php
/**
 * @copyright: DotKernel
 * @library: dot-ems
 * @author: n3vrax
 * Date: 2/10/2017
 * Time: 4:52 PM
 */

declare(strict_types = 1);

namespace Dot\Mapper;

/**
 * Class Utility
 * @package Dot\Ems
 */
class Utility
{
    /**
     * @param array $data
     * @param string $delimiter
     * @return array
     */
    public static function arrayInflate(array $data, string $delimiter = '.'): array
    {
        $output = [];
        foreach ($data as $key => $value) {
            self::arraySet($output, $key, $value);
            if (is_array($value) && !strpos($key, $delimiter)) {
                $nested = self::arrayInflate($value);

                $output[$key] = $nested;
            }
        }
        return $output;
    }

    /**
     * @param array $array
     * @param string $key
     * @param $value
     * @param string $delimiter
     * @return array
     */
    public static function arraySet(array &$array, string $key, $value, string $delimiter = '.'): array
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode($delimiter, $key);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = array();
            }
            $array =& $array[$key];
        }
        $array[array_shift($keys)] = $value;
        return $array;
    }

    /**
     * @param string $string
     * @return string
     */
    public static function underscore(string $string): string
    {
        return self::delimit(str_replace('-', '_', $string), '_');
    }

    /**
     * @param string $string
     * @param string $delimiter
     * @return string
     */
    public static function delimit(string $string, string $delimiter = '_'): string
    {
        return mb_strtolower(preg_replace('/(?<=\\w)([A-Z])/', $delimiter . '\\1', $string));
    }

    /**
     * @param string $string
     * @param string $delimiter
     * @return string
     */
    public static function camelCase(string $string, string $delimiter = '_'): string
    {
        $string = explode($delimiter, $string);
        $string = implode(' ', $string);
        $string = ucwords($string, ' ');
        return str_replace(' ', '', $string);
    }
}
