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

	/**
	 * Check for login
	 *
	 * @return	bool
	 */
	public function perform_check()
	{
		$username = App\Session::get('username');
		$login_hash = App\Session::get('login_hash');

		if ($this->user === null || (is_object($this->user) && $this->user->get('username') != $username))
		{
			$this->user = App\DB::select()->where('username', '=', $username)->from('simpleusers')->execute();
			// this prevents a second check to query again, but will still fail the login_hash check
			if ($this->user->count() == 0)
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

	/**
	 * Login user
	 *
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	public function login($username = '', $password = '')
	{
		$username = trim($username) ?: trim(Input::post('username'));
		$password = trim($password) ?: trim(Input::post('password'));

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

	/**
	 * Logout user
	 *
	 * @return bool
	 */
	public function logout()
	{
		$this->user = null;
		Session::delete('username');
		Session::delete('login_hash');
		return true;
	}

	/**
	 * Create new user
	 *
	 * @param	string
	 * @param	string
	 * @param	string	must contain valid email address
	 * @param	int		group id
	 * @param	array
	 * @return	bool
	 */
	public function create_user($username, $password, $email, $group = 1, Array $profile_fields = array())
	{
		$email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);

		if (empty($username) || empty($password) || empty($email))
		{
			return false;
		}

		$user = array(
			'username'		=> (string) $username,
			'password'		=> $this->hash_password((string) $password),
			'email'			=> $email,
			'group'			=> (int) $group,
			'profile_fields'=> serialize($profile_fields)
		);
		$result = App\DB::insert('simpleusers')
			->set($user)
			->execute();

		return ($result[1] > 0) ? $result[0] : false;
	}

	/**
	 * Update a user's properties
	 * Note: Username cannot be updated, to update password the old password must be passed as old_password
	 *
	 * @param	array	properties to be updated including profile fields
	 * @param	string
	 * @return	bool
	 */
	public function update_user($values, $username = null)
	{
		$username = $username ?: $this->user->get('username');
		$current_values = App\DB::select()
			->where('username', '=', $username)
			->from('simpleusers')->execute();
		if (empty($current_values))
		{
			throw new Auth_Exception('not_found');
		}

		$update = array();
		if (array_key_exists('username', $values))
		{
			throw new Auth_Exception('username_change_not_allowed');
		}
		if (array_key_exists('password', $values))
		{
			if ($current_values->get('password') != $this->hash_password(@$values['old_password']))
			{
				throw new Auth_Exception('invalid_old_password');
			}

			if ( ! empty($values['password']))
			{
				$update['password'] = $this->hash_password($values['password']);
			}
			unset($values['password']);
		}
		if (array_key_exists('email', $values))
		{
			$email = filter_var(trim($values['email']), FILTER_VALIDATE_EMAIL);
			if ( ! $email)
			{
				throw new Auth_Exception('invalid_email');
			}
			$update['email'] = $email;
			unset($values['email']);
		}
		if (array_key_exists('group', $values))
		{
			if (is_numeric($values['group']))
			{
				$update['group'] = (int) $values['group'];
			}
			unset($values['group']);
		}
		if ( ! empty($values))
		{
			$profile_fields = @unserialize($current_values->get('profile_fields')) ?: array();
			foreach ($values as $key => $val)
			{
				if ($val === null)
				{
					unset($profile_fields[$key]);
				}
				else
				{
					$profile_fields[$key] = $val;
				}
			}
			$update['profile_fields'] = $profile_fields;
		}

		$affected_rows = App\DB::update('simpleusers')
			->set($update)
			->where('username', '=', $username)
			->execute();

		return $affected_rows > 0;
	}

	/**
	 * Change a user's password
	 *
	 * @param	string
	 * @param	string
	 * @param	string	username or null for current user
	 * @return	bool
	 */
	public function change_password($old_password, $new_password, $username = null)
	{
		return $this->update_user(array('old_password' => $old_password, 'password' => $new_password), $username);
	}

	/**
	 * Deletes a given user
	 *
	 * @param	string
	 * @return	bool
	 */
	public function delete_user($username)
	{
		if (empty($username))
		{
			throw new Auth_Exception('cannot_delete_empty_username');
		}

		$affected_rows = App\DB::delete('simpleusers')
			->where('username', '=', $username)
			->execute();

		return $affected_rows > 0;
	}

	public function forgotten_password($username)
	{
		$username = $username;
		$user = App\DB::select()
			->where('username', '=', $username)
			->from('simpleusers')->execute();
		if (empty($user))
		{
			throw new Auth_Exception('not_found');
		}

		// MUST GET CODE TO RESET THE PASSWORD TO SOMETHING RANDOM AND EMAIL IT
		// TO THE USER'S EMAILADDRESS
	}

	/**
	 * Creates a temporary hash that will validate the current login
	 *
	 * @return	string
	 */
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

	/**
	 * Get the user's ID
	 *
	 * @return	array	containing this driver's ID & the user's ID
	 */
	public function get_user_id()
	{
		if (empty($this->user))
		{
			return false;
		}

		return array($this->id, (int) $this->user->id);
	}

	/**
	 * Get the user's groups
	 *
	 * @return array	containing the group driver ID & the user's group ID
	 */
	public function get_user_groups()
	{
		if (empty($this->user))
		{
			return false;
		}

		return array(array('simplegroup', $this->user->get('group')));
	}

	/**
	 * Get the user's emailaddress
	 *
	 * @return	string
	 */
	public function get_user_email()
	{
		if (empty($this->user))
		{
			return false;
		}

		return $this->user->get('email');
	}

	/**
	 * Get the user's screen name
	 *
	 * @return	string
	 */
	public function get_user_screen_name()
	{
		if (empty($this->user))
		{
			return false;
		}

		return $this->user->get('username');
	}

	/**
	 * Get the user's profile fields
	 *
	 * @return array
	 */
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
