<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Fuel Development Team
 * @license		MIT License
 * @copyright	2010 Dan Horrigan
 * @link		http://fuelphp.com
 */

class Fuel_Lang {
	
	public static $lines = array();
	
	public static $flat_lines = array();

	public static $fallback = 'en';
	
	public static function load($file, $group = NULL)
	{
		$lang = array();

		// Use the current language, failing that use the fallback language
		foreach (array(Config::get('language'), Lang::$fallback) as $language)
		{
			if ($path = Fuel::find_file('lang/'.$language, $file))
			{
				$lang = Fuel::load($path);
				break;
			}
		}

		if ($group === NULL)
		{
			Lang::$lines = Lang::$lines + $lang;
		}
		else
		{
			if ( ! isset(Lang::$lines[$group]))
			{
				Lang::$lines[$group] = array();
			}
			Lang::$lines[$group] = Lang::$lines[$group] + $lang;
		}
	}

	public static function __($line, $params = array())
	{
		$str = Lang::line($line, $params);

		// Fail? Try translating based on locale
		$str === NULL AND $str = Lang::parse_params(gettext($line), $params);

		// Well f**k you, I'll just return the line then
		empty($str) AND $str = $line;

		return $str;
	}

	public static function line($line, $params = array())
	{
		if (strpos($line, '.') !== FALSE)
		{
			$parts = explode('.', $line);

			$return = FALSE;
			foreach ($parts as $part)
			{
				if ($return === FALSE AND isset(Lang::$lines[$part]))
				{
					$return = Lang::$lines[$part];
				}
				elseif (isset($return[$part]))
				{
					$return = $return[$part];
				}
				else
				{
					return FALSE;
				}
			}
			return  Lang::parse_params($return, $params);
		}

		if (isset(Lang::$lines[$line]))
		{
			return Lang::parse_params(Lang::$lines[$line], $params);
		}
		
		return NULL;
	}

	public function parse_params($string, $array = array())
	{
		$from_arr = array();

		foreach ($array as $from => $to)
		{
			$from_arr[] = ':'.$from;
		}

		return str_replace($from_arr, $array, $string);
	}

	public static function set($line, $value, $group = NULL)
	{
		if ($group === NULL)
		{
			Lang::$lines[$line] = $value;
			return TRUE;
		}
		elseif (isset(Lang::$lines[$group][$line]))
		{
			Lang::$lines[$group][$line] = $value;
			return TRUE;
		}
		return FALSE;
	}
}

/* End of file lang.php */