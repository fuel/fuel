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
		'group_drivers' => array('simplegroup'),
		'login_hash_salt' => 'put_some_salt_in_here',
		'additional_fields' => array('profile_fields')
	);

	public function perform_check()
	{
		$username = App\Session::get('username');
		$login_hash = App\Session::get('login_hash');

		if (empty($this->user) || $this->user->username != $username)
		{
			$this->user = reset(Model\SimpleUser::find_by_username($username, array('limit' => 1)));
			// this prevents a second check to query again, but will still fail the login_hash check
			if (empty($this->user))
			{
				$this->user = new \stdClass();
				$this->user->username = $username;
				$this->user->login_hash = 'none';
			}
		}
		if (empty($this->user) || $this->user->login_hash != $login_hash)
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
		$this->user = Model\SimpleUser::find('first', array(
			'where' => array(array('username', '=', strtolower($username)), array('password', '=', $password))));
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
			throw new Auth_Exception('User not logged in, can\'t create login hash.');
		}

		$this->user->last_login = App\Date::factory()->get_timestamp();
		$this->user->login_hash = sha1($this->config['login_hash_salt'].$this->user->username.$this->user->last_login);
		$this->user->save();
		return $this->user->login_hash;
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

		return array('simplegroup', $this->user->group);
	}

	public function get_user_email()
	{
		if (empty($this->user))
		{
			return false;
		}

		return $this->user->email;
	}

	public function get_user_screen_name()
	{
		if (empty($this->user))
		{
			return false;
		}

		return $this->user->username;
	}

	public function get_profile_fields()
	{
		if (empty($this->user))
		{
			return false;
		}

		return @unserialize($this->user->profile_fields) ?: array();
	}
}