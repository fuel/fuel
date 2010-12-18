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

namespace Fuel\Auth;
use Fuel\App;

// ------------------------------------------------------------------------

/**
 * Auth
 *
 * @package		Fuel
 * @subpackage	Auth
 * @author		Jelmer Schreuder
 */

class Auth {

	/**
	 * @var	Auth_Login_Driver
	 */
	protected static $_instance = null;

	/**
	 * @var	array	contains references if multiple were loaded
	 */
	protected static $_instances = array();

	/**
	 * @var	array	Login drivers that verified a current login
	 */
	protected static $_verified = array();

	/**
	 * @var	bool	Whether to verify multiple
	 */
	protected static $_verify_multiple = false;

	public static function _init()
	{
		App\Config::load('auth', 'auth');

		// Whether to allow multiple drivers of any type, defaults to not allowed
		static::$_verify_multiple = App\Config::get('auth.verify_multiple_logins', false) ? true : false;

		foreach((array) App\Config::get('auth.driver', array()) as $driver => $config)
		{
			$config = is_int($driver)
				? array('driver' => $config)
				: array_merge($config, array('driver' => $driver));
			static::factory($config);
		}
		// set the first (or only) as the default instance for static usage
		if ( ! empty(static::$_instances))
		{
			static::$_instance = reset(static::$_instances);
			static::check();
		}
	}

	/**
	 * Load driver $class to loaded drivers of $type
	 *
	 * @param	string			type of driver
	 * @param	string			driver name
	 * @param	array			settings for the new driver
	 * @throws	Auth_Exception	on driver load failure
	 */
	public static function factory($custom = array())
	{
		// Driver is given as array key or just string in custom
		$custom = ! is_array($custom) ? array('driver' => $custom) : $custom;
		$config = App\Config::get('auth.'.$custom['driver'].'_config', array());
		$config = array_merge($config, $custom);

		// Driver must be set
		if (empty($config['driver']) || ! is_string($config['driver']))
		{
			throw new Session_Exception('No auth driver given.');
		}

		// determine the driver to load
		$driver = App\Auth_Login_Driver::factory($config);

		// get the driver's cookie name
		$id = $driver->get_id();

		// do we already have a driver instance for this cookie?
		if (isset(static::$_instances[$id]))
		{
			// if so, they must be using the same driver class!
			$class = get_class($driver);
			if ( ! static::$_instances[$id] instanceof $class)
			{
				throw new Exception('You can not instantiate two different login drivers using the same id "'.$id.'"');
			}
		}
		else
		{
			// store this instance
			static::$_instances[$id] = $driver;
		}

		return static::$_instances[$id];
	}

	/**
	 * class constructor
	 *
	 * @param	void
	 * @access	private
	 * @return	void
	 */
	final private function __construct() {}

	/**
	 * Remove individual driver, or all drivers of $type
	 *
	 * @param	string	driver id or null for default driver
	 * @throws	Auth_Exception	when $driver_id isn't valid or true
	 */
	public static function unload($driver_id = null)
	{
		if ($driver_id === null && ! empty(static::$_instance))
		{
			unset(static::$_instances[static::$_instance->get_id()]);
			static::$_instance = null;
			return true;
		}
		elseif (array_key_exists($driver_id, static::$_instances))
		{
			return false;
		}

		unset(static::$_instances[$driver_id]);
		return true;
	}

	/**
	 * Return a specific driver, will return (and create if necessary) the default instance
	 * without input.
	 *
	 * @param	string	driver id
	 * @return	Auth_Login_Driver
	 */
	public static function instance($instance = null)
	{
		if ($instance === null)
		{
			if ( ! array_key_exists($instance, static::$_instances))
			{
				throw new Auth_Exception('Unkown instance.');
			}

			return static::$_instance[$instance];
		}

		if (is_null(static::$_instance))
		{
			static::$_instance = static::factory();
		}

		return static::$_instance;
	}

	/**
	 * Check login drivers for validated login
	 *
	 * @param	string|array	specific driver or drivers, in this case it will always terminate after first success
	 * @return	bool
	 */
	public static function check($specific = null)
	{
		$drivers = $specific === null ? static::$_instances : (array) $drivers;

		foreach ($drivers as $i)
		{
			if ( ! static::$_verify_multiple && ! empty(static::$_verified))
			{
				return true;
			}

			$i = $i instanceof Auth_Login_Driver ? $i : static::instance($i);
			if ( ! array_key_exists($i->get_id(), static::$_verified))
			{
				$i->check();
			}

			if ($specific)
			{
				if (array_key_exists($i->get_id(), static::$_verified))
				{
					return true;
				}
			}
		}

		return $specific === null && ! empty(static::$_verified);
	}

	/**
	 * Get verified driver or all verified drivers
	 * returns false when specific driver has not validated
	 * when all were requested and none validated an empty array is returned
	 *
	 * @param	null|string	driver id or null for all verified driver in an array
	 * @return	array|Auth_Login_Driver|false
	 */
	public static function verified($driver = null)
	{
		if ($driver === null)
		{
			return static::$_verified;
		}

		if ( ! array_key_exists($driver, static::$_verified))
		{
			return false;
		}

		return static::$_verified[$driver];
	}

	/**
	 * Register verified Login driver
	 *
	 * @param	Auth_Login_Driver
	 */
	public static function _register_verified(Auth_Login_Driver $driver)
	{
		static::$_verified[$driver->get_id()] = $driver;
	}

	/**
	 * Unregister verified Login driver
	 *
	 * @param	Auth_Login_Driver
	 */
	public static function _unregister_verified(Auth_Login_Driver $driver)
	{
		unset(static::$_verified[$driver->get_id()]);
	}

	/**
	 * Retrieve a loaded group driver instance
	 * (loading must be done by Auth class)
	 *
	 * @param	string|true		driver id or true for an array of all loaded drivers
	 * @return	Auth_Group_Driver|array
	 */
	public static function group($instance)
	{
		return App\Auth_Group_Driver::instance($instance);
	}

	/**
	 * Verify Group membership
	 *
	 * @param	mixed	group identifier to check for membership
	 * @param	string	group driver id or null to check all
	 * @param	array	user identifier to check in form array(driver_id, user_id)
	 * @return bool
	 */
	public static function member($group, $driver = null, $user = null)
	{
		if ($driver === null)
		{
			if ($user === null)
			{
				foreach (static::$_verified as $v)
				{
					if ($v->member($group))
					{
						return true;
					}
				}
			}
			else
			{
				foreach (static::$_instances as $i)
				{
					if ($i->member($group, null, $user))
					{
						return true;
					}
				}
			}
			return false;
		}
		else
		{
			if ($user === null)
			{
				foreach (static::$_verified as $v)
				{
					if (static::group($driver)->member($group))
					{
						return true;
					}
				}
			}
			elseif (static::group($driver)->member($group, $user))
			{
				return true;
			}

			return false;
		}
	}

	/**
	 * Retrieve a loaded acl driver instance
	 * (loading must be done by Auth class)
	 *
	 * @param	string|true		driver id or true for an array of all loaded drivers
	 * @return	Auth_Acl_Driver|array
	 */
	public static function acl($instance)
	{
		return App\Auth_Acl_Driver::instance($instance);
	}

	/**
	 * Verify Acl access
	 *
	 * @param	mixed	condition to validate
	 * @param	string	acl driver id or null to check all
	 * @param	array	user or group identifier to check in form array(driver_id, id)
	 * @return	bool
	 */
	public static function has_access($condition, $driver = null, $entity = null)
	{
		if ($driver === null)
		{
			if ($entity === null)
			{
				foreach (static::$_verified as $v)
				{
					if ($v->has_access($condition))
					{
						return true;
					}
				}
			}
			else
			{
				foreach (static::$_instances as $i)
				{
					if ($i->has_access($condition, null, $entity))
					{
						return true;
					}
				}
			}
			return false;
		}
		else
		{
			if ($entity === null)
			{
				foreach (static::$_verified as $v)
				{
					if (static::acl($driver)->has_access($condition))
					{
						return true;
					}
				}
			}
			elseif (static::acl($driver)->has_access($condition, $entity))
			{
				return true;
			}

			return false;
		}
	}
}

/* end of file auth.php */