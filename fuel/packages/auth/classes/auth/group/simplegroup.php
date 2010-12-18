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

class Auth_Group_SimpleGroup extends Auth_Group_Driver {

	public static $_valid_groups = array();

	public static function _init()
	{
		static::$_valid_groups = array_keys(App\Config::get('simpleauth.groups'));
	}

	protected $config = array(
		'drivers' => array('acl' => array('simpleacl'))
	);

	public function member($group, $user = null)
	{
		if ($user === null)
		{
			$groups = Auth::instance()->get_user_groups();
		}
		else
		{
			// to be written...
			// $groups = Auth::instance($user[0])->get_user_groups();
		}

		if ( ! $groups || ! in_array((int) $group, static::$_valid_groups))
		{
			return false;
		}

		return in_array(array($this->id, $group), $groups);
	}

	public function get_name($group)
	{
		return @static::$_valid_groups[(int) $group]['name'] ?: false;
	}

	public function get_roles($group)
	{
		if ( ! in_array((int) $group, static::$_valid_groups))
		{
			return false;
		}

		$groups = Config::get('simpleauth.groups');
		return $groups[(int) $group]['roles'];
	}
}

/* end of file simplegroup.php */