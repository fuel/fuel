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

abstract class Auth_Driver {

	/**
	 * @var	Auth_Login_Driver
	 */
	protected static $_instance = null;

	/**
	 * @var	array	contains references if multiple were loaded
	 */
	protected static $_instances = array();

	public static function factory(Array $config = array())
	{
		throw new Auth_Exception('Driver must have a factory method extension.');
	}

	// ------------------------------------------------------------------------

	/**
	 * @var	string	instance identifier
	 */
	protected $id;

	/**
	 * @var	array	given configuration array
	 */
	protected $config = array();

	protected function __construct(Array $config)
	{
		$this->id = $config['id'];
		$this->config = array_merge($this->config, $config);
	}

	/**
	 * Get driver instance ID
	 *
	 * @return string
	 */
	public function get_id()
	{
		return (string) $this->id;
	}

	/**
	 * Create or change config value
	 *
	 * @param	string
	 * @param	mixed
	 */
	public function set_config($key, $value)
	{
		$this->config[$key] = $value;
	}

	/**
	 * Retrieve config value
	 *
	 * @param	string
	 * @return	mixed
	 */
	public function get_config($key)
	{
		return $this->config[$key];
	}
}

/* end of file driver.php */