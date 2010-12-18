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

abstract class Auth_Group_Driver extends App\Auth_Driver {

	public static function factory(Array $config = array())
	{
		// default driver id to driver name when not given
		! array_key_exists('id', $config) && $config['id'] = $config['driver'];

		$class = 'App\\Auth_Group_'.ucfirst($config['driver']);
		$driver = new $class($config);

		if (array_key_exists('acl_drivers', $config))
		{
			foreach ($config['acl_drivers'] as $driver => $config)
			{
				$config = is_int($driver)
					? array('driver' => $config)
					: array_merge($config, array('driver' => $driver));
				$class = 'App\\Auth_Acl_'.$config['driver'];
				$class::factory($config);
			}
		}

		return $driver;
	}

	// ------------------------------------------------------------------------

	/**
	 * Verify Acl access
	 *
	 * @param	mixed	condition to validate
	 * @param	string	acl driver id or null to check all
	 * @param	array	user identifier to check in form array(driver_id, user_id)
	 * @return	bool
	 */
	public function has_access($condition, $driver, $group = null)
	{
		// When group was given just check the group
		if (is_array($group))
		{
			if ($driver === null)
			{
				foreach (App\Auth::acl(true) as $acl)
				{
					if ($acl->has_access($condition, $group))
					{
						return true;
					}
				}

				return false;
			}

			return App\Auth::acl($driver)->has_access($condition, $group);
		}

		// When no group was given check all logged in users
		foreach (App\Auth::verified() as $v)
		{
			// ... and check all those their groups
			$gs = $v->get_user_groups();
			foreach ($gs as $g_id)
			{
				// ... and try to validate if its group is this one
				if ($this instanceof $g_id[0])
				{
					if ($this->has_access($condition, $driver, $g_id))
					{
						return true;
					}
				}
			}
		}

		// when nothing validated yet: it has failed to
		return false;
	}

	// ------------------------------------------------------------------------

	/**
	 * Check membership of given users
	 *
	 * @param	mixed	condition to check for access
	 * @param	array	user identifier in the form of array(driver_id, user_id), or null for logged in
	 * @return	bool
	 */
	abstract public function member($group, $user = null);

	/**
	 * Fetch the display name of the given group
	 *
	 * @param	mixed	group condition to check
	 * @return	string
	 */
	abstract public function get_name($group);
}

/* end of file driver.php */