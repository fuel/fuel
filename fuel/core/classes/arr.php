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
	 * WARNING: original array is edited by reference, only boolean success is returned
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
			throw new App\Exception('Arr::elements() - $keys must be an array.');
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

	/**
	 * Insert value(s) into an array, mostly an array_splice alias
	 * WARNING: original array is edited by reference, only boolean success is returned
	 *
	 * @param	array		The original array (by reference)
	 * @param	array|mixed	The value(s) to insert, if you want to insert an array it needs to be in an array itself
	 * @param	int			The numeric position at which to insert, negative to count from the end backwards
	 * @return	bool		false when array shorter then $pos, otherwise true
	 */
	public static function insert(Array &$original, $value, $pos)
	{
		if (count($original) < abs($pos))
		{
			App\Error::notice('Position larger than number of elements in array in which to insert.');
			return false;
		}

		array_splice($original, $pos, 0, $value);
		return true;
	}

	/**
	 * Insert value(s) into an array after a specific key
	 * WARNING: original array is edited by reference, only boolean success is returned
	 *
	 * @param	array		The original array (by reference)
	 * @param	array|mixed	The value(s) to insert, if you want to insert an array it needs to be in an array itself
	 * @param	string|int	The key after which to insert
	 * @return	bool		false when key isn't found in the array, otherwise true
	 */
	public static function insert_after_key(Array &$original, $value, $key)
	{
		$pos = array_search($key, array_keys($original));
		if ($pos === false)
		{
			App\Error::notice('Unknown key after which to insert the new value into the array.');
			return false;
		}

		return static::insert($original, $value, $pos + 1);
	}

	/**
	 * Insert value(s) into an array after a specific value (first found in array)
	 *
	 * @param	array		The original array (by reference)
	 * @param	array|mixed	The value(s) to insert, if you want to insert an array it needs to be in an array itself
	 * @param	string|int	The value after which to insert
	 * @return	bool		false when value isn't found in the array, otherwise true
	 */
	public static function insert_after_value(Array &$original, $value, $search)
	{
		$key = array_search($search, $original);
		if ($key === false)
		{
			App\Error::notice('Unknown value after which to insert the new value into the array.');
			return false;
		}

		return static::insert_after_key($original, $value, $key);
	}
}

/* End of file arr.php */
