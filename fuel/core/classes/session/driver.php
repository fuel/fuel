<?php
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

namespace Fuel\Core;

use Fuel\App as App;

abstract class Session_Driver {

	/*
	 * @var	session class configuration
	 */
	protected $config = array();

	/*
	 * @var	session indentification keys
	 */
	protected $keys = array();

	/*
	 * @var	session variable data
	 */
	protected $data = array();

	/*
	 * @var	session flash data
	 */
	protected $flash = array();

	/*
	 * @var	session time object
	 */
	protected $time = null;

	// --------------------------------------------------------------------
	// abstract methods
	// --------------------------------------------------------------------

	/**
	 * create a new session
	 *
	 * @access	public
	 * @return	void
	 */
	abstract function create();

	// --------------------------------------------------------------------

	/**
	 * read the session
	 *
	 * @access	public
	 * @return	void
	 */
	abstract function read();

	// --------------------------------------------------------------------

	/**
	 * write the session
	 *
	 * @access	public
	 * @return	void
	 */
	abstract function write();

	// --------------------------------------------------------------------

	/**
	 * destroy the current session
	 *
	 * @access	public
	 * @return	void
	 */
	abstract function destroy();

	// --------------------------------------------------------------------
	// generic driver methods
	// --------------------------------------------------------------------

	/**
	 * generic driver initialisation
	 *
	 * @access	public
	 * @return	void
	 */
	public function init()
	{
		// get a time object
		$this->time = App\Date::time();
	}

	// --------------------------------------------------------------------

	/**
	 * set session variables
	 *
	 * @param	string	name of the variable to set
	 * @param	mixed	value
	 * @access	public
	 * @return	void
	 */
	public function set($name, $value = false)
	{
		$this->data[$name] = $value;

		// need to auto-update the session?
		if ($this->config['write_on_set'] === true)
		{
			$this->write();
		}
	}

	// --------------------------------------------------------------------

	/**
	 * get session variables
	 *
	 * @access	public
	 * @param	string	name of the variable to get
	 * @param	mixed	default value to return if the variable does not exist
	 * @return	mixed
	 */
	public function get($name, $default = null)
	{
		if (isset($this->data[$name]))
		{
			return $this->data[$name];
		}

		if (strpos($name, '.') !== false)
		{
			$parts = explode('.', $name);

			switch (count($parts))
			{
				case 2:
					if (isset($this->data[$parts[0]][$parts[1]]))
					{
						return $this->data[$parts[0]][$parts[1]];
					}
				break;

				case 3:
					if (isset($this->data[$parts[0]][$parts[1]][$parts[2]]))
					{
						return $this->data[$parts[0]][$parts[1]][$parts[2]];
					}
				break;

				case 4:
					if (isset($this->data[$parts[0]][$parts[1]][$parts[2]][$parts[3]]))
					{
						return $this->data[$parts[0]][$parts[1]][$parts[2]][$parts[3]];
					}
				break;

				default:
					$return = false;
					foreach ($parts as $part)
					{
						if ($return === false and isset($this->data[$part]))
						{
							$return = $this->data[$part];
						}
						elseif (isset($return[$part]))
						{
							$return = $return[$part];
						}
						else
						{
							return $default;
						}
					}
					return $return;
				break;
			}
		}

		return $default;
	}

	// --------------------------------------------------------------------

	/**
	 * get session key variables
	 *
	 * @access	public
	 * @param	string	name of the variable to get, default is 'session_id'
	 * @return	mixed	contents of the requested variable, or false if not found
	 */
	public function key($name)
	{
		return isset($this->keys[$name]) ? $this->keys[$name] : false;
	}

	// --------------------------------------------------------------------

	/**
	 * delete session variables
	 *
	 * @param	string	name of the variable to delete
	 * @param	mixed	value
	 * @access	public
	 * @return	void
	 */
	public function delete($name)
	{
		if (isset($this->data[$name]))
		{
			unset($this->data[$name]);
		}

		if (strpos($name, '.') !== false)
		{
			$parts = explode('.', $name);

			switch (count($parts))
			{
				case 2:
					if (isset($this->data[$parts[0]][$parts[1]]))
					{
						unset($this->data[$parts[0]][$parts[1]]);
					}
				break;

				case 3:
					if (isset($this->data[$parts[0]][$parts[1]][$parts[2]]))
					{
						unset($this->data[$parts[0]][$parts[1]][$parts[2]]);
					}
				break;

				case 4:
					if (isset($this->data[$parts[0]][$parts[1]][$parts[2]][$parts[3]]))
					{
						unset($this->data[$parts[0]][$parts[1]][$parts[2]][$parts[3]]);
					}
				break;

				default:
					$return = false;
					foreach ($parts as $part)
					{
						if ($return === false and isset($this->data[$part]))
						{
							$return =& $this->data[$part];
						}
						elseif (isset($return[$part]))
						{
							$return =& $return[$part];
						}
					}
					if ($return !== false) unset($return);
				break;
			}
		}

		// need to auto-update the session?
		if ($this->config['write_on_set'] === true)
		{
			$this->write();
		}
	}

	// --------------------------------------------------------------------

	/**
	 * force a session_id rotation
	 *
	 * @access	public
	 * @param	boolean, if true, force a session id rotation
	 * @return  void
	 */
	public function rotate($force = true)
	{
		// existing session. need to rotate the session id?
		if ($this->config['rotation_time'] &&
			($force or $this->keys['created'] + $this->config['rotation_time'] <= $this->time->get_timestamp()))
		{

			// generate a new session id, and update the create timestamp
			$this->keys['previous_id']	= $this->keys['session_id'];
			$this->keys['session_id']	= $this->_new_session_id();
			$this->keys['created'] 		= $this->time->get_timestamp();
			$this->keys['updated']		= $this->keys['created'];
		}

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
		$this->flash[$this->config['flash_id'].'::'.$name] = array('state' => 'new', 'value' => $value);

		// need to auto-update the session?
		if ($this->config['write_on_set'] === true)
		{
			$this->write();
		}
	}

	// --------------------------------------------------------------------

	/**
	 * get session flash variables
	 *
	 * @access	public
	 * @param	string	name of the variable to get
	 * @param	mixed	default value to return if the variable does not exist
	 * @return	mixed
	 */
	public function get_flash($name, $default = null)
	{
		if (isset($this->flash[$this->config['flash_id'].'::'.$name]))
		{
			$this->flash[$this->config['flash_id'].'::'.$name]['state'] = '';
			return $this->flash[$this->config['flash_id'].'::'.$name]['value'];
		}
		return $default;
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
		if (isset($this->flash[$this->config['flash_id'].'::'.$name]))
		{
			$this->flash[$this->config['flash_id'].'::'.$name]['state'] = 'new';
		}

		// need to auto-update the session?
		if ($this->config['write_on_set'] === true)
		{
			$this->write();
		}
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
		if (isset($this->flash[$this->config['flash_id'].'::'.$name]))
		{
			unset($this->flash[$this->config['flash_id'].'::'.$name]);
		}

		// need to auto-update the session?
		if ($this->config['write_on_set'] === true)
		{
			$this->write();
		}
	}

	// --------------------------------------------------------------------

	/**
	 * set the session flash id
	 *
	 * @param	string	name of the id to set
	 * @access	public
	 * @return	void
	 */
	public function set_flash_id($name)
	{
		$this->config['flash_id'] = (string) $name;
	}

	// --------------------------------------------------------------------

	/**
	 * get the current session flash id
	 *
	 * @access	public
	 * @return	string	name of the flash id
	 */
	public function get_flash_id($name)
	{
		return $this->config['flash_id'];
	}

	// --------------------------------------------------------------------

	/**
	 * get a runtime config value
	 *
	 * @param	string	name of the config variable to get
	 * @access	public
	 * @return  mixed
	 */
	public function get_config($name)
	{
		return isset($this->config[$name]) ? $this->config[$name] : null;
	}

	// --------------------------------------------------------------------

	/**
	 * removes flash variables marked as old
	 *
	 * @access	private
	 * @return  void
	 */
	protected function _cleanup_flash()
	{
		foreach($this->flash as $key => $value)
		{
			if ($value['state'] === '')
			{
				unset($this->flash[$key]);
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * mark all flash as used so they will be expired
	 *
	 * @access	private
	 * @return  void
	 */
	protected function _mark_flash()
	{
		foreach($this->flash as $key => $value)
		{
			$this->flash[$key]['state'] = '';
		}

		// need to auto-update the session?
		if ($this->config['write_on_set'] === true)
		{
			$this->write();
		}
	}

	// --------------------------------------------------------------------

	/**
	 * generate a new session id
	 *
	 * @access	private
	 * @return  void
	 */
	protected function _new_session_id()
	{
		$session_id = '';
		while (strlen($session_id) < 32)
		{
			$session_id .= mt_rand(0, mt_getrandmax());
		}
		return md5(uniqid($session_id, TRUE));
	}

	// --------------------------------------------------------------------

	/**
	 * write a cookie
	 *
	 * @access	private
	 * @param	array, cookie payload
	 * @return  void
	 */
	 protected function _set_cookie($payload = array())
	 {
		// record the last update time of the session
		$this->keys['updated'] = $this->time->get_timestamp();

		// add the session keys to the payload
		array_unshift($payload, $this->keys);

		// encrypt the payload
		$payload = App\Crypt::encode($this->_serialize($payload));

		// make sure it doesn't exceed the cookie size specification
		if (strlen($payload) > 4000)
		{
			throw new App\Exception('The session data stored by the application in the cookie exceeds 4Kb. Select a different session storage driver.');
		}

		// write the session cookie
		if ($this->config['expire_on_close'])
		{
			return App\Cookie::set($this->config['cookie_name'], $payload, 0);
		}
		else
		{
			return App\Cookie::set($this->config['cookie_name'], $payload, $this->config['expiration_time']);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * read a cookie
	 *
	 * @access	private
	 * @return  void
	 */
	 protected function _get_cookie()
	 {
		// fetch the cookie
		$cookie = App\Cookie::get($this->config['cookie_name'], false);

		// if not found, check for the post_cookie variable
		if ($cookie === false)
		{
			$cookie = App\Input::get_post($this->config['post_cookie_name'], false);
		}

		if ($cookie !== false)
		{
			// fetch the payload
			$cookie = $this->_unserialize(App\Crypt::decode($cookie));

			// validate the cookie
			if ( ! isset($cookie[0]) )
			{
				// not a valid cookie payload
			}
			elseif ($cookie[0]['updated'] + $this->config['expiration_time'] <= $this->time->get_timestamp())
			{
				// session has expired
			}
			elseif ($this->config['match_ip'] && $cookie[0]['ip_address'] !== App\Input::real_ip())
			{
				// IP address doesn't match
			}
			elseif ($this->config['match_ua'] && $cookie[0]['user_agent'] !== App\Input::user_agent())
			{
				// user agent doesn't match
			}
			else
			{
				// session is valid, retrieve the session keys
				if (isset($cookie[0])) $this->keys = $cookie[0];

				// and return the cookie payload
				array_shift($cookie);
				return $cookie;
			}
		}

		// no payload
		return FALSE;
	 }

	// --------------------------------------------------------------------

	/**
	 * Serialize an array
	 *
	 * This function first converts any slashes found in the array to a temporary
	 * marker, so when it gets unserialized the slashes will be preserved
	 *
	 * @access	private
	 * @param	array
	 * @return	string
	 */
	protected function _serialize($data)
	{
		if (is_array($data))
		{
			foreach ($data as $key => $val)
			{
				if (is_string($val))
				{
					$data[$key] = str_replace('\\', '{{slash}}', $val);
				}
			}
		}
		else
		{
			if (is_string($data))
			{
				$data = str_replace('\\', '{{slash}}', $data);
			}
		}

		return serialize($data);
	}

	// --------------------------------------------------------------------

	/**
	 * Unserialize
	 *
	 * This function unserializes a data string, then converts any
	 * temporary slash markers back to actual slashes
	 *
	 * @access	private
	 * @param	array
	 * @return	string
	 */
	protected function _unserialize($data)
	{
		$data = @unserialize(stripslashes($data));

		if (is_array($data))
		{
			foreach ($data as $key => $val)
			{
				if (is_string($val))
				{
					$data[$key] = str_replace('{{slash}}', '\\', $val);
				}
			}

			return $data;
		}

		return (is_string($data)) ? str_replace('{{slash}}', '\\', $data) : $data;
	}

	// --------------------------------------------------------------------

	/**
	 * validate__config
	 *
	 * This function validates all global (driver independent) configuration values
	 *
	 * @access	private
	 * @param	array
	 * @return	array
	 */
	protected function _validate_config($config)
	{
		$validated = array();

		foreach ($config as $name => $item)
		{
			switch($name)
			{
				case 'driver':
					// if we get here, this one was ok... ;-)
				break;

				case 'match_ip':
					// make sure it's a boolean
					$item = (bool) $item;
				break;

				case 'match_ua':
					// make sure it's a boolean
					$item = (bool) $item;
				break;

				case 'cookie_domain':
					// make sure it's a string
					$item = (string) $item;
				break;

				case 'cookie_path':
					// make sure it's a string
					$item = (string) $item;
					if (empty($item))
					{
						$item = '/';
					}
				break;

				case 'expire_on_close':
					// make sure it's a boolean
					$item = (bool) $item;
				break;

				case 'expiration_time':
					// make sure it's an integer
					$item = (int) $item;
					if ($item <= 0)
					{
						// invalid? set it to two years from now
						$item = 86400 * 365 * 2;
					}
				break;

				case 'rotation_time':
					// make sure it's an integer
					$item = (int) $item;
					if ($item <= 0)
					{
						// invalid? set it to 5 minutes
						$item = 300;
					}
				break;

				case 'flash_id':
					// make sure it's a string
					$item = (string) $item;
					if (empty($item))
					{
						$item = 'flash';
					}
				break;

				case 'flash_auto_expire':
					// make sure it's a boolean
					$item = (bool) $item;
				break;

				case 'write_on_set':
					// make sure it's a boolean
					$item = (bool) $item;
				break;

				case 'post_cookie_name':
					// make sure it's a string
					$item = (string) $item;
				break;

				default:
					// ignore this setting
				break;

			}

			// store the validated result
			$validated[$name] = $item;
		}

		return $validated;
	}

}

/* End of file driver.php */
