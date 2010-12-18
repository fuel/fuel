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

abstract class Auth_Acl_Driver extends Auth_Driver {

	public static function factory(Array $config = array())
	{
		// default driver id to driver name when not given
		! array_key_exists('id', $config) && $config['id'] = $config['driver'];

		$class = 'Fuel\\Auth\\Auth_Acl_'.ucfirst($config['driver']);
		$driver = new $class($config);

		return $driver;
	}

	// ------------------------------------------------------------------------

	/**
	 * Check access rights
	 *
	 * @param	mixed	condition to check for access
	 * @param	mixed	user or group identifier in the form of array(driver_id, id)
	 * @return	bool
	 */
	abstract protected function has_access($condition, Array $entity);
}

/* end of file driver.php */