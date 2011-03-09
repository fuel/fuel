<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package        Fuel
 * @version        1.0
 * @author        Fuel Development Team
 * @license        MIT License
 * @copyright    2010 - 2011 Fuel Development Team
 * @link        http://fuelphp.com
 */

namespace Fuel\Core;

/**
 * String handling with encoding support
 * 
 * PHP needs to be compiled with --enable-mbstring
 * or a fallback without encoding support is used
 */
class Str {

    /**
     * Add's _1 to a string or increment the ending number to allow _2, _3, etc
     *
     * @param string $str required
     * @return string
     */
    public static function increment($str, $first = 1)
    {
        preg_match('/(.+)_([0-9]+)$/', $str, $match);

        return isset($match[2]) ? $match[1].'_'.($match[2] + 1) : $str.'_'.$first;
    }

    /**
     * lower
     *
     * @param string $str required
     * @param string $encoding default UTF-8
     * @return string
     */
    public static function lower($str, $encoding = null)
    {
        $encoding or $encoding = \Fuel::$encoding;

        return function_exists('mb_strtolower')
            ? mb_strtolower($str, $encoding)
            : strtolower($str);
    }

    /**
     * upper
     *
     * @param string $str required
     * @param string $encoding default UTF-8
     * @return string
     */
    public static function upper($str, $encoding = null)
    {
        $encoding or $encoding = \Fuel::$encoding;

        return function_exists('mb_strtoupper')
            ? mb_strtoupper($str, $encoding)
            : strtoupper($str);
    }

    /**
     * lcfirst
     * 
     * Does not strtoupper first
     *
     * @param string $str required
     * @param string $encoding default UTF-8
     * @return string 
     */
    public static function lcfirst($str, $encoding = null)
    {
        $encoding or $encoding = \Fuel::$encoding;

        return function_exists('mb_strtolower')
            ? mb_strtolower(mb_substr($str, 0, 1, $encoding), $encoding).
                mb_substr($str, 1, mb_strlen($str, $encoding), $encoding)
            : ucfirst($str);
    }

    /**
     * ucfirst
     *
     * Does not strtolower first
     * 
     * @param string $str required
     * @param string $encoding default UTF-8
     * @return string 
     */
    public static function ucfirst($str, $encoding = null)
    {
        $encoding or $encoding = \Fuel::$encoding;

        return function_exists('mb_strtoupper')
            ? mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding).
                mb_substr($str, 1, mb_strlen($str, $encoding), $encoding)
            : ucfirst($str);
    }

    /**
     * ucwords
     * 
     * First strtolower then ucwords
     * 
     * ucwords normally doesn't strtolower first
     * but MB_CASE_TITLE does, so ucwords now too
     * 
     * @param string $str required
     * @param string $encoding default UTF-8
     * @return string 
     */
    public static function ucwords($str, $encoding = null)
    {
        $encoding or $encoding = \Fuel::$encoding;

        return function_exists('mb_convert_case')
            ? mb_convert_case($str, MB_CASE_TITLE, $encoding)
            : ucwords(strtolower($str));
    }
}

/* End of file str.php */