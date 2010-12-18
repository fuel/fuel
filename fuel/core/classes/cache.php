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

class Cache {

	/**
	 * Loads any default caching settings when available
	 *
	 * @access	public
	 */
	public static function _init()
	{
		App\Config::load('cache', true);
	}

	// ---------------------------------------------------------------------

	/**
	 * Creates a new cache instance.
	 *
	 * @access	public
	 * @param	mixed			The identifier of the cache, can be anything but empty
	 * @param	array|string	Either an array of settings or the storage driver to be used
	 * @return	object			The new request
	 */
	public static function factory($identifier, $config = array())
	{
		// load the default config
		$defaults = App\Config::get('cache', array());

		// $config can be either an array of config settings or the name of the storage driver
		if ( ! empty($config) && ! is_array($config) && ! is_null($config))
		{
			$config = array('driver' => $config);
		}

		// Overwrite default values with given config
		$config = array_merge($defaults, (array) $config);

		if (empty($config['driver']))
		{
			throw new App\Exception('No cache driver given or no default cache driver set.');
		}

		$class = 'Fuel\\App\\Cache_Storage_'.ucfirst($config['driver']);

		// Convert the name to a string when necessary
		$identifier = call_user_func($class.'::stringify_identifier', $identifier);

		// Return instance of the requested cache object
		return new $class($identifier, $config);
	}

	// ---------------------------------------------------------------------

	/**
	 * Front for writing the cache, ensures interchangebility of storage drivers. Actual writing
	 * is being done by the _set() method which needs to be extended.
	 *
	 * @access	public
	 * @param	mixed			The identifier of the cache, can be anything but empty
	 * @param	mixed			The content to be cached
	 * @param	int				The time in minutes until the cache will expire, =< 0 or null means no expiration
	 * @param	array			Contains the identifiers of caches this one will depend on (not supported by all drivers!)
	 * @return	object			The new request
	 */
	public static function set($identifier, $contents = null, $expiration = null, $dependencies = array())
	{
		$cache = static::factory($identifier);
		return $cache->set($contents, $expiration, $dependencies);
	}

	// ---------------------------------------------------------------------

	/**
	 * Does get() & set() in one call that takes a callback and it's arguements to generate the contents
	 *
	 * @access	public
	 * @param	mixed			The identifier of the cache, can be anything but empty
	 * @param	string|array	Valid PHP callback
	 * @param	array 			Arguements for the above function/method
	 * @param	int				Cache expiration in minutes
	 * @param	array			Contains the identifiers of caches this one will depend on (not supported by all drivers!)
	 */
	public static function call($identifier, $callback, $args = array(), $expiration = null, $dependencies = array())
	{
		$cache = static::factory($identifier);
		return $cache->call($callback, $args, $expiration, $dependencies);
	}

	// ---------------------------------------------------------------------

	/**
	 * Front for reading the cache, ensures interchangebility of storage drivers. Actual reading
	 * is being done by the _get() method which needs to be extended.
	 *
	 * @access	public
	 * @param	mixed			The identifier of the cache, can be anything but empty
	 * @param	bool
	 * @return	mixed
	 */
	public static function get($identifier, $use_expiration = true)
	{
		$cache = static::factory($identifier);
		return $cache->get($use_expiration);
	}

	// ---------------------------------------------------------------------

	/**
	 * Frontend for deleting item from the cache, interchangable storage methods. Actual operation
	 * handled by delete() call on storage driver class
	 *
	 * @access	public
	 * @param	mixed			The identifier of the cache, can be anything but empty
	 * @return	mixed
	 */
	public static function delete($identifier)
	{
		$cache = static::factory($identifier);
		return $cache->delete();
	}

	// ---------------------------------------------------------------------

	/**
	 * Flushes the whole cache for a specific storage driver or just a part of it when $section is set
	 * (might not work with all storage drivers), defaults to the default storage driver
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 */
	public static function delete_all($section = null, $driver = null)
	{
		$cache = static::factory('__NOT_USED__', $driver);
		return $cache->delete_all($section);
	}
}

/* End of file cache.php */
