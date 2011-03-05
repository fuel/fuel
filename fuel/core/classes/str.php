<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Fuel Development Team
 * @license		MIT License
 * @copyright	2010 - 2011 Fuel Development Team
 * @link		http://fuelphp.com
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

    /**
	 * random
	 *
	 * Generates a random string with a given length
	 *
	 * @param int $len string length
	 * @return string
	 */
    public function random($len)
    {
        $random = '';
        srand((double)microtime() * 1000000);

        $data  = "AbcDE123IJKLMN67QRSTUVWXYZ";
        $data .= "aBCdefghijklmn123opq45rs67tuv89wxyz";
        $data .= "0FGH45OP89";

        for ($i = 0; $i < $len; $i++)
        {
            $random .= substr($data, (rand() % ( strlen($data) )), 1);
        }

        return $random;
    }
}

/* End of file str.php */