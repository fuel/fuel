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

// ------------------------------------------------------------------------

/**
 * Validation
 *
 * Static object to allow static usage of validation through singleton.
 *
 * @package		Fuel
 * @subpackage	Core
 * @category	Core
 * @author		Jelmer Schreuder
 */
class Validation {

	protected static $_instance = null;

	public static function factory()
	{
		return new Validation_Object();
	}

	public static function instance()
	{
		if (is_null(static::$_instance))
		{
			static::$_instance = static::factory();
		}
		
		return static::$_instance;
	}

	final private function __construct() {}

	public static function add_field($field, $title = null, Array $rules = array())
	{
		return static::instance()->add_field($field, $title, $rules);
	}

	public static function add_model($model)
	{
		return static::instance()->add_model($model);
	}

	public static function add_callable($class)
	{
		return static::instance()->add_callable($class);
	}

	public static function run($input = null)
	{
		return static::instance()->run($input);
	}

	public static function validated($field = false, $default = false)
	{
		return static::instance()->validated($field, $default);
	}

	public static function errors($field = false, $default = false)
	{
		return static::instance()->errors($field, $default);
	}
}

/* End of file validation.php */