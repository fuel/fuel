<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Fuel
 * @version		1.0
 * @license		MIT License
 * @copyright	2010 Dan Horrigan
 * @link		http://fuelphp.com
 */

namespace Fuel;

// --------------------------------------------------------------------

/**
 * Session Class
 *
 * @package		Fuel
 * @subpackage	Core
 * @category	Core
 * @author		Harro "WanWizard" Verton
 */

// --------------------------------------------------------------------

class Session
{
	/*
	 * loaded session driver instance
	 */
	protected static $instance = false;

	/*
	 * list of supported session drivers
	 */
	protected static $valid_storage = array('cookie', 'file', 'memcached', 'db');

	// --------------------------------------------------------------------
	// session initialisation methods
	// --------------------------------------------------------------------

	/*
	 * class uses as a static object, automatically read everything
	 */
	public static function _init($parms = array())
	{
		// executed when loading the class, or when instatiating an object
		if (isset($this) OR ( ! isset($this) AND static::$instance === false))
		{
			// load the session configuration
			if ( ! empty($parms) && is_array($parms))
			{
				$config = $parms;
			}
			else
			{
				Config::load('session', 'session');
				$config = Config::get('session');
			}

			// if the parm is a string, it's the desired session type
			if (is_string($parms))
			{
				$config['driver'] = $parms;
			}

			// set and validate the selected session driver
			if ( ! isset($config['driver']))
			{
				$config['driver'] = isset($config['default']) ? $config['default'] : NULL;
			}

			if ( ! in_array($config['driver'], static::$valid_storage))
			{
				throw new Exception('You have specified an invalid session storage system.');
			}

			// instantiate the driver
			$driver = 'Session_'.ucfirst($config['driver']).'_Driver';
			static::$instance = new $driver;

			// and configure it, specific driver config first
			if (isset($config[$config['driver']]))
			{
				static::$instance->set_config('config', $config[$config['driver']]);
			}

			// then load globals and/or set defaults
			self::set_initial_config('driver', $config['driver']);
			self::set_initial_config('match_ip', isset($config['match_ip']) ? (bool) $config['match_ip'] : true);
			self::set_initial_config('match_ua', isset($config['match_ua']) ? (bool) $config['match_ua'] : true);
			self::set_initial_config('cookie_name', isset($config['cookie_name']) ? (string) $config['cookie_name'] : 'fuelsession');
			self::set_initial_config('cookie_domain', isset($config['cookie_domain']) ? (string) $config['cookie_domain'] : '');
			self::set_initial_config('cookie_path', isset($config['cookie_path']) ? (string) $config['cookie_path'] : '/');
			self::set_initial_config('expire_on_close', isset($config['expire_on_close']) ? (bool) $config['expire_on_close'] : false);
			self::set_initial_config('expiration_time', isset($config['expiration_time']) ? ((int) $config['expiration_time'] > 0 ? (int) $config['expiration_time'] : 86400*365*2) : 7200);
			self::set_initial_config('rotation_time', isset($config['rotation_time']) ? (int) $config['rotation_time'] : 300);
			self::set_initial_config('flash_id', isset($config['flash_id']) ? (string) $config['flash_id'] : 'flash');
			self::set_initial_config('flash_auto_expire', isset($config['flash_auto_expire']) ? (bool) $config['flash_auto_expire'] : true);
			self::set_initial_config('write_on_finish', isset($config['write_on_finish']) ? (bool) $config['write_on_finish'] : false);

			// if the driver has an init method, call it
			if (method_exists(static::$instance, 'init'))
			{
				static::$instance->init();
			}

			// load the session
			self::read();
		}

		return static::$instance;
	}

	// --------------------------------------------------------------------

	/*
	 * set initial config values, if not already defined
	 */
	private static function set_initial_config($name, $value)
	{
		if ( is_null(static::$instance->get_config($name)))
		{
			static::$instance->set_config($name, $value);
		}
	}

	// --------------------------------------------------------------------

	/*
	 * class autoload initialisation, and driver instantiation
	 */
	public function __construct($config = array())
	{
		self::_init($config);
	}

	// --------------------------------------------------------------------

	/*
	 * allows instantiation of a named session driver
	 */
	public static function factory($config = NULL)
	{
		// reset the current instance
		static::$instance = false;

		// run the instance initialisation again, return the instance
		return self::_init($config);
	}

	// --------------------------------------------------------------------
	// mapping of the static public methods to the driver instance methods
	// --------------------------------------------------------------------

	/**
	 * set session variables
	 *
	 * @param	string	name of the variable to set
	 * @param	mixed	value
	 * @access	public
	 * @return	void
	 */
	public static function set($name, $value)
	{
		$return = static::$instance->set($name, $value);

		// Automatically write if static
		(isset($this) && 'Fuel_'.get_class($this) == __CLASS__) OR self::write();

		return $return;
	}

	// --------------------------------------------------------------------

	/**
	 * get session variables
	 *
	 * @access	public
	 * @param	string	name of the variable to get
	 * @return	mixed
	 */
	public static function get($name)
	{
		return static::$instance->get($name);
	}

	// --------------------------------------------------------------------

	/**
	 * delete a session variable
	 *
	 * @param	string	name of the variable to delete
	 * @param	mixed	value
	 * @access	public
	 * @return	void
	 */
	public static function delete($name)
	{
		$return = static::$instance->delete($name);

		// Automatically write if static
		('Fuel_'.get_class($this) == __CLASS__) OR self::write();

		return $return;
	}

	// --------------------------------------------------------------------

	/**
	 * set session flash variables
	 *
	 * @param	string	name of the variable to set
	 * @param	mixed	value
	 * @access	public
	 * @return	void
	 */
	public static function set_flash($name, $value)
	{
		$return = static::$instance->set_flash($name, $value);

		// Automatically write if static
		(isset($this) && 'Fuel_'.get_class($this) == __CLASS__) OR self::write();

		return $return;
	}

	// --------------------------------------------------------------------

	/**
	 * get session flash variables
	 *
	 * @access	public
	 * @param	string	name of the variable to get
	 * @return	mixed
	 */
	public static function get_flash($name)
	{
		return static::$instance->get_flash($name);
	}

	// --------------------------------------------------------------------

	/**
	 * keep session flash variables
	 *
	 * @access	public
	 * @param	string	name of the variable to keep
	 * @return	void
	 */
	public static function keep_flash($name)
	{
		$return = static::$instance->keep_flash($name);

		// Automatically write if static
		('Fuel_'.get_class($this) == __CLASS__) OR self::write();

		return $return;
	}

	// --------------------------------------------------------------------

	/**
	 * delete session flash variables
	 *
	 * @param	string	name of the variable to delete
	 * @param	mixed	value
	 * @access	public
	 * @return	void
	 */
	public static function delete_flash($name)
	{
		$return = static::$instance->delete_flash($name);

		// Automatically write if static
		('Fuel_'.get_class($this) == __CLASS__) OR self::write();

		return $return;
	}

	// --------------------------------------------------------------------

	/**
	 * create a new session
	 *
	 * @access	public
	 * @return	void
	 */
	public static function create()
	{
		return static::$instance->create();
	}

	// --------------------------------------------------------------------

	/**
	 * read the session
	 *
	 * @access	public
	 * @return	void
	 */
	public static function read()
	{
		return static::$instance->read();
	}

	// --------------------------------------------------------------------

	/**
	 * write the session
	 *
	 * @access	public
	 * @return	void
	 */
	public static function write()
	{
		return static::$instance->write();
	}

	// --------------------------------------------------------------------

	/**
	 * destroy the current session
	 *
	 * @access	public
	 * @return	void
	 */
	public static function destroy()
	{
		return static::$instance->destroy();
	}

}

/* End of file session.php */
