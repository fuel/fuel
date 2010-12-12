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

use Fuel\Application as App;

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

	/**
	 * Creates a new cache instance.
	 *
	 * @access	public
	 * @param	mixed			The identifier of the cache, can be anything but empty
	 * @param	array|string	Either an array of settings or the storage engine to be used
	 * @return	object			The new request
	 */
	public static function factory($identifier, $config = array())
	{
		// $config can be either an array of config settings or the name of the storage engine
		if ( ! is_array($config))
		{
			$config = array('driver' => (string) $config);
		}

		// Set the extending storage type class
		if (array_key_exists('storage', $config))
		{
			$storage = $config['storage'];
			unset($config['storage']);
		}
		else
		{
			$storage = App\Config::get('cache.storage', 'file');
		}
		$class = 'App\\Cache_Storage_'.ucfirst($storage);

		// Convert the name to a string when necessary
		$identifier = call_user_func($class.'::stringify_identifier', $identifier);

		// Return instance of the requested cache object
		return new $class($identifier, $config);
	}

	/**
	 * Front for writing the cache, ensures interchangebility of storage engines. Actual writing
	 * is being done by the _set() method which needs to be extended.
	 *
	 * @access	public
	 * @param	mixed			The content to be cached
	 * @param	int				The time in minutes until the cache will expire, =< 0 or null means no expiration
	 * @param	array			Array of names on which this cache depends for
	 * @return	object			The new request
	 */
	public static function set($identifier, $contents = null, $expiration = null, $dependencies = array())
	{
		$cache = static::factory($identifier);
		return $cache->set($contents, $expiration, $dependencies);
	}

	/**
	 * Does get() & set() in one call that takes a callback and it's arguements to generate the contents
	 *
	 * @access	public
	 * @param	string|array	Valid PHP callback
	 * @param	array 			Arguements for the above function/method
	 * @param	int				Cache expiration in minutes
	 * @param	array			Contains the identifiers of caches this one will depend on
	 */
	public static function call($callback, $args = array(), $expiration = null, $dependencies = array())
	{
		// simplify the identifier to the classname when applicable, otherwise serialization is unnecessarily heavy
		$identifier = (is_array($callback) && is_object($callback[0])) ? array(get_class($callback[0]), $callback[1]) : $callback;
		$cache = static::factory($identifier);
		return $cache->call($callback, $args, $expiration, $dependencies);
	}

	/**
	 * Front for reading the cache, ensures interchangebility of storage engines. Actual reading
	 * is being done by the _get() method which needs to be extended.
	 *
	 * @access	public
	 * @param	bool
	 * @return	mixed
	 */
	public static function get($identifier, $use_expiration = true)
	{
		$cache = static::factory($identifier);
		return $cache->get($use_expiration);
	}

	/**
	 * Frontend for deleting item from the cache, interchangable storage methods. Actual operation
	 * handled by delete() call on storage driver class
	 *
	 * @access	public
	 * @param	string
	 * @return	mixed
	 */
	public static function delete($identifier)
	{
		$cache = static::factory($identifier);
		return $cache->delete();
	}

	/**
	 * Flushes the whole cache for a specific storage type or just a part of it when $section is set (might not work
	 * with all storage drivers), defaults to the default storage type
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 */
	final public static function delete_all($section = null, $storage = null)
	{
		if ( empty( $storage ) )
		{
			$storage = Config::get('cache.storage', 'file');
		}
		$class = 'App\\Cache_Storage_'.ucfirst($storage);

		$identifier = call_user_func_array($class.'::_delete_all', array($section));
	}
}

/* End of file cache.php */
