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

namespace Fuel;

class Arr {
	
	/**
	 * Flattens a multi-dimensional associative array down into a 1 dimensional
	 * assoc. array.
	 *
	 * @access	public
	 * @param	array	The array to flatten
	 * @param	string	What to glue the keys together with
	 * @param	bool	Whether to reset and start over on a new array
	 * @return	array
	 */
	public static function flatten_assoc($array, $glue = ':', $reset = true)
	{
		static $return = array();
		static $curr_key = array();

		if ($reset)
		{
			$return = array();
			$curr_key = array();
		}

		foreach ($array as $key => $val)
		{
			$curr_key[] = $key;
			if (is_array($val) and array_values($val) !== $val)
			{
				static::flatten_assoc($val, $glue, false);
			}
			else
			{
				$return[implode($glue, $curr_key)] = $val;
			}
			array_pop($curr_key);
		}
		return $return;
	}

	/**
	 * Returns the element of the given array or a default if it is not set.
	 *
	 * @access	public
	 * @param	array	The array to fetch from
	 * @param	mixed	The key to fetch from the array
	 * @param	mixed	The value returned when not an array or invalid key
	 * @return	mixed
	 */
	public static function element($array, $key, $default = false)
	{
		if ( ! is_array($array) || ! array_key_exists($key, $array))
		{
			return $default;
		}
		
		return $array[$key];
	}

	/**
	 * Returns the elements of the given array or a default if it is not set.
	 *
	 * @access	public
	 * @param	array	The array to fetch from
	 * @param	array	The keys to fetch from the array
	 * @param	array	The value returned when not an array or invalid key
	 * @return	mixed
	 */
	public static function elements($array, $keys, $default = false)
	{
		$return = array();
		
		if ( ! is_array($keys))
		{
			throw new Exception('Arr::elements() - $keys must be an array.');
		}
		
		foreach ($keys as $key)
		{
			if ( ! array_key_exists($key, $array))
			{
				$return[$key] = $default;
			}
			else
			{
				$return[$key] = $array[$key];
			}
		}
		
		return $return;
	}
}

/* End of file arr.php */