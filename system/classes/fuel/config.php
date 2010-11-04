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
	
	public static function get($item, $group = false)
	{
		if (strpos($item, '.') !== false)
		{
			$parts = explode('.', $item);

			$return = false;
			foreach ($parts as $part)
			{
				if ($return === false and isset(Config::$items[$part]))
				{
					$return = Config::$items[$part];
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
			return $return;
		}

		if (isset(Config::$items[$item]))
		{
			return Config::$items[$item];
		}
		return false;
	}

	public static function set($item, $value)
	{
		$parts = explode('.', $item);

		$item =& Config::$items;
		foreach ($parts as $part)
		{
			// if it's not an array it can't have a subvalue
			if ( ! is_array($item))
			{
				return false;
			}
			
			// if the part didn't exist yet: add it
			if ( ! isset($item[$part]))
			{
				$item[$part] = array();
			}
			
			$item =& $item[$part];
		}
		$item = $value;
		
		return true;
	}
}

/* End of file config.php */