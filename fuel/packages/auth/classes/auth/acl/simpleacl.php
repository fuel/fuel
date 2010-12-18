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

	protected function has_access($condition, Array $entity)
	{
		$group = App\Auth::group($entity[0]);
		if ( ! is_array($condition) || empty($group) || ! is_callable($group, 'get_roles'))
		{
			return false;
		}

		$roles = $group->get_roles($entity[1]);
		$role = $condition[0];
		$rights = $condition[1];

		$current_rights = '';
		foreach($roles as $r_role => $r_rights)
		{
			if ($r_rights === false)
			{
				return false;
			}
			elseif ($r_rights === true)
			{
				$current_rights = 'crud';
			}
			elseif ($role == $r_role)
			{
				$current_rights .= $r_rights;
			}
		}

		// start checking rights, terminate false when character not found
		foreach($rights as $right)
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