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

	protected static $language = '';

	public static function load($file, $group = null)
	{
		static::$language = \Config::get('language');

		$lang = array();

		// Use the current language, failing that use the fallback language
		foreach (array(static::$language, static::$fallback) as $language)
		{
			$lang[$language] = array();

			if ($path = \Fuel::find_file('lang/'.$language, $file, '.php', true))
			{
				foreach ($path as $p)
				{
					$lang[$language] = $lang[$language] + \Fuel::load($p);
				}
			}

			if ( ! isset(static::$lines[$language]))
			{
				static::$lines[$language] = array();
			}

			if ($group === null)
			{
				static::$lines[$language] = static::$lines[$language] + $lang[$language];
			}
			else
			{
				$group = ($group === true) ? $file : $group;

				if ( ! isset(static::$lines[$language][$group]))
				{
					static::$lines[$language][$group] = array();
				}

				static::$lines[$language][$group] = static::$lines[$language][$group] + $lang[$language];
			}

			// Loading same language again not needed
			if (static::$language == static::$fallback)
			{
				break;
			}
		}
	}

	public static function line($line, $params = array())
	{
		// Use the current language, failing that use the fallback language
		foreach (array(static::$language, static::$fallback) as $i => $language)
		{
			if ( ! isset(static::$lines[$language]))
			{
				continue;
			}

			if (strpos($line, '.') !== false)
			{
				$parts = explode('.', $line);

				$return = false;
				foreach ($parts as $part)
				{
					if ($return === false and isset(static::$lines[$language][$part]))
					{
						$return = static::$lines[$language][$part];
					}
					elseif (isset($return[$part]))
					{
						$return = $return[$part];
					}
					elseif ($i == 0)
					{
						continue(2);
					}
					else
					{
						$return = array_pop($parts);

						break;
					}
				}

				return static::_parse_params($return, $params);
			}

			if (isset(static::$lines[$language][$line]))
			{
				$line = static::$lines[$language][$line];

				break;
			}
		}

		return static::_parse_params($line, $params);
	}

	public static function set($line, $value, $group = null)
	{
		if ($group === null)
		{
			static::$lines[static::$language][$line] = $value;
			return true;
		}
		elseif (isset(static::$lines[static::$language][$group][$line]))
		{
			static::$lines[static::$language][$group][$line] = $value;
			return true;
		}
		return false;
	}

	protected function _parse_params($string, $array = array())
	{
		$tr_arr = array();

		foreach ($array as $from => $to)
		{
			$tr_arr[':'.$from] = $to;
		}
		unset($array);

		return strtr($string, $tr_arr);
	}
}

/* End of file lang.php */