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
			static::$items = static::$items + $config;
		}
		else
		{
			if ( ! isset(static::$items[$group]))
			{
				static::$items[$group] = array();
			}
			static::$items[$group] = static::$items[$group] + $config;
		}
	}
	
	public static function get($item, $default = false)
	{
		if (strpos($item, '.') !== false)
		{
			$parts = explode('.', $item);

			$return = false;
			foreach ($parts as $part)
			{
				if ($return === false and isset(static::$items[$part]))
				{
					$return = static::$items[$part];
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

		if (isset(static::$items[$item]))
		{
			return static::$items[$item];
		}
		return $default;
	}

	public static function set($item, $value)
	{
		$parts = explode('.', $item);

		$item =& static::$items;
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