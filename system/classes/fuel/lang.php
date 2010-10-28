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

	public static function __($line, $group = FALSE)
	{
		$str = Lang::line($line, $group);

		// Fail? Try translating based on locale
		$str === NULL AND $str = gettext($line);

		// Well f**k you, I'll just return the line then
		empty($str) AND $str = $line;

		return $str;
	}

	public static function line($line, $group = FALSE)
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
			return $return;
		}

		if (isset(Lang::$lines[$line]))
		{
			return Lang::$lines[$line];
		}
		
		return NULL;
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