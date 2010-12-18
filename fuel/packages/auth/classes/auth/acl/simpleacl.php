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

class Auth_Acl_SimpleAcl extends Auth_Acl_Driver {

	protected static $_valid_roles = array();

	public static function _init()
	{
		static::$_valid_roles = array_keys(App\Config::get('simpleauth.roles'));
	}

	public function has_access($condition, Array $entity)
	{
		$group = Auth::group($entity[0]);
		if ( ! is_array($condition) || empty($group) || ! is_callable(array($group, 'get_roles')))
		{
			return false;
		}

		$area = $condition[0];
		$rights = $condition[1];
		$current_roles = $group->get_roles($entity[1]);
		$current_rights = '';
		if (is_array($current_roles))
		{
			$roles = Config::get('simpleauth.roles', array());
			array_key_exists('#', $roles) && array_unshift($current_roles, '#');
			foreach ($current_roles as $r_role)
			{
				if ( ! array_key_exists($r_role, $roles) || ($r_rights = $roles[$r_role]) === false)
				{
					return false;
				}

				if (array_key_exists($area, $r_rights))
				{
					$current_rights = ($r_rights === true || $current_rights === true)
						? true
						: $current_rights . $r_rights[$area];
				}
			}
		}

		// start checking rights, terminate false when character not found
		$rights = array_unique(preg_split('//', $rights, -1, PREG_SPLIT_NO_EMPTY));
		foreach ($rights as $right)
		{
			if (strpos($current_rights, $right) === false)
			{
				return false;
			}
		}

		return true;
	}
}

/* end of file simpleacl.php */