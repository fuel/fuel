<?php defined('COREPATH') or die('No direct script access.');
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Harro "WanWizard" Verton
 * @license		MIT License
 * @copyright	2010 Dan Horrigan
 * @link		http://fuelphp.com
 */

// --------------------------------------------------------------------

class Fuel_Session
{
	/*
	 * loaded session driver instance
	 */
	protected static $instance = false;

	/*
	 * list of supported session drivers
	 */
	protected static $valid_storage = array('cookie', 'file', 'memcached');

	// --------------------------------------------------------------------

	/*
	 * class uses as a static object, automatically read everything
	 */
	public function _init(array $config = array())
	{
		// If loaded as an instance or first load of static
		if (isset($this) OR ( ! isset($this) AND self::$instance === false))
		{
			// load the session configuration
			if (empty($config) OR ! is_array($config))
			{
				Config::load('session', 'session');
				$config = Config::get('session');
			}

			// validate the config, set some defaults if needed
			if ( ! isset($config['type']) OR ! in_array($config['type'], self::$valid_storage))
			{
				throw new Fuel_Exception('You have specified an invalid session storage system.');
			}

			// instantiate the driver
			$driver = 'Session_'.ucfirst($config['type']).'_Driver';
			self::$instance = new $driver;

			// and configure it
			self::$instance->set_config('match_ip', isset($config['match_ip']) ? (bool) $config['match_ip'] : true);
			self::$instance->set_config('match_ua', isset($config['match_ua']) ? (bool) $config['match_ua'] : true);
			self::$instance->set_config('cookie_name', isset($config['cookie_name']) ? (string) $config['cookie_name'] : 'fuelsession');
			self::$instance->set_config('cookie_domain', isset($config['cookie_domain']) ? (string) $config['cookie_domain'] : '');
			self::$instance->set_config('cookie_path', isset($config['cookie_path']) ? (string) $config['cookie_path'] : '/');
			self::$instance->set_config('expiration_time', isset($config['expiration_time']) ? (int) $config['expiration_time'] : 0);
			self::$instance->set_config('rotation_time', isset($config['rotation_time']) ? (int) $config['rotation_time'] : 300);
			self::$instance->set_config('flash_id', isset($config['flash_id']) ? (string) $config['flash_id'] : 'flash');
			self::$instance->set_config('config', isset($config['config']) ? (array) $config['config'] : array());
			self::$instance->set_config('flash_auto_expire', isset($config['flash_auto_expire']) ? (bool) $config['flash_auto_expire'] : true);
			self::$instance->set_config('write_on_finish', isset($config['write_on_finish']) ? (bool) $config['write_on_finish'] : false);

			// if the driver has an init method, call it
			if (method_exists(self::$instance, 'init'))
			{
				self::$instance->init();
			}

			// and load the session
			self::read();
		}
	}

	/*
	 * class autoload initialisation, and driver instantiation
	 */
	public function __construct(array $config = array())
	{
		self::_init($config);
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
	public function set($name, $value)
	{
		$return = self::$instance->set($name, $value);

		// Automatically write if status
		('Fuel_'.get_class($this) == __CLASS__) OR self::write();

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
	public function get($name)
	{
		return self::$instance->get($name);
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
	public function delete($name)
	{
		$return = self::$instance->delete($name);

		// Automatically write if status
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
	public function set_flash($name, $value)
	{
		$return = self::$instance->set_flash($name, $value);

		// Automatically write if status
		('Fuel_'.get_class($this) == __CLASS__) OR self::write();

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
	public function get_flash($name)
	{
		return self::$instance->get_flash($name);
	}

	// --------------------------------------------------------------------

	/**
	 * keep session flash variables
	 *
	 * @access	public
	 * @param	string	name of the variable to keep
	 * @return	void
	 */
	public function keep_flash($name)
	{
		$return = self::$instance->keep_flash($name);

		// Automatically write if status
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
	public function delete_flash($name)
	{
		$return = self::$instance->delete_flash($name);

		// Automatically write if status
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
	public function create()
	{
		return self::$instance->create();
	}

	// --------------------------------------------------------------------

	/**
	 * read the session
	 *
	 * @access	public
	 * @return	void
	 */
	public function read()
	{
		return self::$instance->read();
	}

	// --------------------------------------------------------------------

	/**
	 * write the session
	 *
	 * @access	public
	 * @return	void
	 */
	public function write()
	{
		return self::$instance->write();
	}

	// --------------------------------------------------------------------

	/**
	 * destroy the current session
	 *
	 * @access	public
	 * @return	void
	 */
	public function destroy()
	{
		return self::$instance->destroy();
	}

}

/* End of file session.php */
