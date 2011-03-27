<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2011 Fuel Development Team
 * @link       http://fuelphp.com
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
	 * Truncates a string to the given length.  It will optionally preserve
	 * HTML tags if $is_html is set to true.
	 *
	 * @param   string  $string        the string to truncate
	 * @param   int     $limit         the number of characters to truncate too
	 * @param   string  $continuation  the string to use to denote it was truncated
	 * @param   bool    $is_html       whether the string has HTML
	 * @return  string  the truncated string
	 */
	public static function truncate($string, $limit, $continuation = '...', $is_html = false)
	{
		$offset = 0;
		$tags = array();
		if ($is_html)
		{
			preg_match_all('/<[^>]+>([^<]*)/', $string, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
			foreach ($matches as $match)
			{
				if($match[0][1] - $offset >= $limit)
				{
					break;
				}
				$tag = substr(strtok($match[0][0], " \t\n\r\0\x0B>"), 1);
				if($tag[0] != '/')
				{
					$tags[] = $tag;
				}
				elseif (end($tags) == substr($tag, 1))
				{
					array_pop($tags);
				}
				$offset += $match[1][1] - $match[0][1];
			}
		}
		$new_string = substr($string, 0, $limit = min(strlen($string),  $limit + $offset));
		$new_string .= (strlen($string) > $limit ? $continuation : '');
		$new_string .= (count($tags = array_reverse($tags)) ? '</'.implode('></',$tags).'>' : '');
		return $new_string;
	}

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