<?php defined('COREPATH') or die('No direct script access.');
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

	public static function line($line, $params = array())
	{
		if (strpos($line, '.') !== false)
		{
			$parts = explode('.', $line);

			$return = false;
			foreach ($parts as $part)
			{
				if ($return === false and isset(Lang::$lines[$part]))
				{
					$return = Lang::$lines[$part];
				}
				elseif (isset($return[$part]))
				{
					$return = $return[$part];
				}
				else
				{
					return false;
				}
			}
			return  Lang::parse_params($return, $params);
		}

		isset(Lang::$lines[$line]) and $line = Lang::$lines[$line];
		
		return Lang::parse_params($line, $params);
	}

	public function parse_params($string, $array = array())
	{
		$tr_arr = array();

		foreach ($array as $from => $to)
		{
			$tr_arr[':'.$from] = $to;
		}
		unset($array);

		return strtr($string, $tr_arr);
	}

	public static function set($line, $value, $group = NULL)
	{
		if ($group === NULL)
		{
			Lang::$lines[$line] = $value;
			return true;
		}
		elseif (isset(Lang::$lines[$group][$line]))
		{
			Lang::$lines[$group][$line] = $value;
			return true;
		}
		return false;
	}
}

/* End of file lang.php */