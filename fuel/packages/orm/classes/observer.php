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

namespace Orm;

abstract class Observer {

	protected static $_instance = array();

	public static function orm_notify($instance, $event)
	{
		if (method_exists(static::instance(), $event))
		{
			static::instance()->{$event}($instance);
		}
	}

	public static function instance()
	{
		$class = get_called_class();

		if (empty(static::$_instance[$class]))
		{
			static::$_instance[$class] = new static;
		}

		return static::$_instance[$class];
	}
}

/* End of file observer.php */