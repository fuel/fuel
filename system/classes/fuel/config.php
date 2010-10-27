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

class Fuel_Config {
	
	public static $items = array();
	
	public static $flat_items = array();
	
	public static function load($file, $group = NULL)
	{
		$config = array();
		if ($path = Fuel::find_file('config', $file))
		{
			$config = Fuel::load($path);
		}
		if ($group === NULL)
		{
			Config::$items = Config::$items + $config;
		}
		else
		{
			if ( ! isset(Config::$items[$group]))
			{
				Config::$items[$group] = array();
			}
			Config::$items[$group] = Config::$items[$group] + $config;
		}
	}
	
	public static function get($item, $group = FALSE)
	{
		if (strpos($item, '.') !== FALSE)
		{
			$parts = explode('.', $item);

			$return = FALSE;
			foreach ($parts as $part)
			{
				if ($return === FALSE AND isset(Config::$items[$part]))
				{
					$return = Config::$items[$part];
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

		if (isset(Config::$items[$item]))
		{
			return Config::$items[$item];
		}
		return FALSE;
	}

	public static function set($item, $value, $group = NULL)
	{
		if ($group === NULL)
		{
			Config::$items[$item] = $value;
			return TRUE;
		}
		elseif (isset(Config::$items[$group][$item]))
		{
			Config::$items[$group][$item] = $value;
			return TRUE;
		}
		return FALSE;
	}
}

/* End of file config.php */