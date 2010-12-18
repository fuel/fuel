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

/**
 * This class is based off the Singleton class in php-activerecord (MIT License)
 */
abstract class Singleton
{
	/**
	 * The instances cache.
	 *
	 * @var array
	 */
	private static $instances = array();

	/**
	 * Instantiates the Singleton class.
	 *
	 * @return object
	 */
	final public static function instance()
	{
		$class = get_called_class();

		if ( ! isset(self::$instances[$class]))
		{
			self::$instances[$class] = new $class;
		}

		return self::$instances[$class];
	}

	/**
	 * Singleton objects should not be cloned.
	 *
	 * @return void
	 */
	final private function __clone() {}

	/**
	 * Similar to a get_called_class() for a child class to invoke.
	 *
	 * @return string
	 */
	final protected function get_called_class()
	{
		$backtrace = debug_backtrace();
    	return get_class($backtrace[2]['object']);
	}
}

/* End of file singleton.php */
