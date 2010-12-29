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
 * @copyright	2010 Dan Horrigan
 * @link		http://fuelphp.com
 */

namespace Fuel\Core;

use Fuel\App as App;

class Lang {

	public static $lines = array();

	public static $flat_lines = array();

	public static $fallback = 'en';

	public static function load($file, $group = NULL)
	{
		$lang = array();

		// Use the current language, failing that use the fallback language
		foreach (array(App\Config::get('language'), static::$fallback) as $language)
		{
			if ($path = App\Fuel::find_file('lang/'.$language, $file, '.php', true))
			{
				$lang = array();
				foreach ($path as $p)
				{
					$lang = $lang + App\Fuel::load($p);
				}
				break;
			}
		}

		if ($group === NULL)
		{
			static::$lines = static::$lines + $lang;
		}
		else
		{
			if ( ! isset(static::$lines[$group]))
			{
				static::$lines[$group] = array();
			}
			static::$lines[$group] = static::$lines[$group] + $lang;
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
				if ($return === false and isset(static::$lines[$part]))
				{
					$return = static::$lines[$part];
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
			return  static::parse_params($return, $params);
		}

		isset(static::$lines[$line]) and $line = static::$lines[$line];

		return static::parse_params($line, $params);
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
			static::$lines[$line] = $value;
			return true;
		}
		elseif (isset(static::$lines[$group][$line]))
		{
			static::$lines[$group][$line] = $value;
			return true;
		}
		return false;
	}
}

/* End of file lang.php */
