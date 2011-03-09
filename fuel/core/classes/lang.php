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
 * Lang Class
 *
 * @package		Fuel
 * @category	Core
 * @author		Phil Sturgeon
 * @link		http://fuelphp.com/docs/classes/lang.html
 */
class Lang {

	public static $lines = array();

	public static $flat_lines = array();

	public static $fallback = 'en';

	public static function load($file, $group = null)
	{
		$lang = array();

		// Use the current language, failing that use the fallback language
		foreach (array(\Config::get('language'), static::$fallback) as $language)
		{
			if ($path = \Fuel::find_file('lang/'.$language, $file, '.php', true))
			{
				$lang = array();
				foreach ($path as $p)
				{
					$lang = $lang + \Fuel::load($p);
				}
				break;
			}
		}

		if ($group === null)
		{
			static::$lines = static::$lines + $lang;
		}
		else
		{
			$group = ($group === true) ? $file : $group;
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
			return  static::_parse_params($return, $params);
		}

		isset(static::$lines[$line]) and $line = static::$lines[$line];

		return static::_parse_params($line, $params);
	}

	public static function set($line, $value, $group = null)
	{
		if ($group === null)
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

	protected static function _parse_params($string, $array = array())
	{
		if (is_string($string))
		{
			$tr_arr = array();

			foreach ($array as $from => $to)
			{
				$tr_arr[':'.$from] = $to;
			}
			unset($array);

			return strtr($string, $tr_arr);
		}
		else
		{
			return $string;
		}
	}
}

/* End of file lang.php */
