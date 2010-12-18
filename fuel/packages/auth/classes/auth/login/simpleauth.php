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

/*
	CREATE TABLE `simpleusers` (
		`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`username` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
		`password` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
		`group` INT NOT NULL DEFAULT 1 ,
		`email` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
		`last_login` VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
		`login_hash` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
		`profile_fields` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
		UNIQUE (
			`username` ,
			`email`
		)
	)
*/

class Auth_Login_SimpleAuth extends Auth_Login_Driver {

	public static function _init()
	{
		App\Config::load('simpleauth', true);
	}

	/**
	 * @var	Model\SimpleUser
	 */
	protected $user;

	/**
	 * @var	array	SimpleAuth config
	 */
	protected $config = array(
		'salt_prefix' => '',
		'salt_postfix' => '',
		'drivers' => array('group' => array('simplegroup')),
		'login_hash_salt' => 'put_some_salt_in_here',
		'additional_fields' => array('profile_fields')
	);

	public function perform_check()
	{
		$username = App\Session::get('username');
		$login_hash = App\Session::get('login_hash');

		if ($this->user === null || (is_object($this->user) && $this->user->username != $username))
		{
			$this->user = App\DB::select()->where('username', '=', $username)->from('simpleusers')->execute();
			// this prevents a second check to query again, but will still fail the login_hash check
			if (empty($this->user))
			{
				$this->user = false;
			}
		}
		if (empty($this->user) || $this->user->get('login_hash') != $login_hash)
		{
			return false;
		}

		return true;
	}

	public function login()
	{
		$username = trim(Input::post('username'));
		$password = trim(Input::post('password'));

		if (empty($username) || empty($password))
		{
			return false;
		}

		$password = $this->hash_password($password);
		$this->user = App\DB::select()
				->where('username', '=', $username)
				->where('password', '=', $password)
				->from('simpleusers')->execute();
		if (empty($this->user))
		{
			return false;
		}

		Session::set('username', $username);
		Session::set('login_hash', $this->create_login_hash());
		return true;
	}

	public function logout()
	{
		$this->user = null;
		Session::delete('username');
		Session::delete('login_hash');
		return true;
	}

	public function create_login_hash()
	{
		if (empty($this->user))
		{
			throw new App\Auth_Exception('User not logged in, can\'t create login hash.');
		}

		$last_login = App\Date::factory()->get_timestamp();
		$login_hash = sha1($this->config['login_hash_salt'].$this->user->get('username').$last_login);

		App\DB::update('simpleusers')
			->set(array('last_login' => $last_login, 'login_hash' => $login_hash))
			->where('username', '=', $this->user->get('username'))->execute();

		return $login_hash;
	}

	public function get_user_id()
	{
		if (empty($this->user))
		{
			return false;
		}

		return array($this->id, $this->user->id);
	}

	public function get_user_groups()
	{
		if (empty($this->user))
		{
			return false;
		}

		return array(array('simplegroup', $this->user->get('group')));
	}

	public function get_user_email()
	{
		if (empty($this->user))
		{
			return false;
		}

		return $this->user->get('email');
	}

	public function get_user_screen_name()
	{
		if (empty($this->user))
		{
			return false;
		}

		return $this->user->get('username');
	}

	public function get_profile_fields()
	{
		if (empty($this->user))
		{
			return false;
		}

		return @unserialize($this->user->get('profile_fields')) ?: array();
	}

	/**
	 * Extension of base driver method to default to user group instead of user id
	 */
	public function has_access($condition, $driver = null, $user = null)
	{
		if (is_null($user))
		{
			$user = reset($this->get_user_groups());
		}
		return parent::has_access($condition, $driver, $user);
	}
}
