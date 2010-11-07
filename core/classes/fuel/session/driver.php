<?php defined('COREPATH') or die('No direct script access.');
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

// --------------------------------------------------------------------

class Fuel_Session_Driver {

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

	// --------------------------------------------------------------------
	// driver generic methods
	// --------------------------------------------------------------------

	/**
	 * create a new session
	 *
	 * @access	public
	 * @return	void
	 */
	public function create()
	{
		$this->_set_cookie();
	}

	// --------------------------------------------------------------------

	/**
	 * read the session
	 *
	 * @access	public
	 * @return	void
	 */
	public function read()
	{
		// do we need to read
		if (empty($this->keys))
		{
			// fetch the session cookie
			if( ! $this->_get_cookie())
			{
				// create a new session
				$this->create();
			}

			// auto expire all flash variables if required
			if ($this->config['flash_auto_expire'])
			{
				$this->_mark_flash();
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * write the session
	 *
	 * @access	public
	 * @return	void
	 */
	public function write()
	{
		static $write_on_finish_event = false;

		// do we need to set a write_on_finish event?
		if ($this->config['write_on_finish'])
		{
			// check if we need to register the shutdown event
			if ( ! $write_on_finish_event)
			{
				// register a shutdown event to update the session
				Shutdown::event(array($this, 'write_session'));
				$write_on_finish_event = true;
			}
		}
		else
		{
			$this->write_session();
		}
	}

	// --------------------------------------------------------------------

	/**
	 * destroy the current session
	 *
	 * @access	public
	 * @return	void
	 */
	public function destroy()
	{
		// delete the session cookie
		Cookie::delete($this->config['cookie_name']);

		// and reset all session storage
		$this->keys = array();
		$this->data = array();
		$this->flash = array();
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
	public function set($name, $value)
	{
		$this->data[$name] = $value;
	}

	// --------------------------------------------------------------------

	/**
	 * get session variables
	 *
	 * @access	public
	 * @param	string	name of the variable to get
	 * @return	mixed
	 */
	public function get($name)
	{
		return isset($this->data[$name]) ? $this->data[$name] : false;
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
	}

	// --------------------------------------------------------------------

	/**
	 * get session flash variables
	 *
	 * @access	public
	 * @param	string	name of the variable to get
	 * @return	mixed
	 */
	public function get_flash($name)
	{
		if (isset($this->flash[$this->config['flash_id'].'::'.$name]))
		{
			$this->flash[$this->config['flash_id'].'::'.$name]['state'] = '';
			return $this->flash[$this->config['flash_id'].'::'.$name]['value'];
		}
		return FALSE;
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
	 * set a runtime config value
	 *
	 * @param	string	name of the config variable to set
	 * @param	mixed	value
	 * @access	public
	 * @return  mixed
	 */
	public function set_config($name, $value)
	{
		switch ($name)
		{
			// booleans
			case 'match_ip':
			case 'match_ua':
			case 'flash_auto_expire':
			case 'write_on_finish':
				$this->config[$name] = (bool) $value;
				break;
			// strings
			case 'flash_id':
			case 'cookie_name':
			case 'cookie_domain':
			case 'cookie_path':
				$this->config[$name] = (string) $value;
				break;
			// integers
			case 'expiration_time':
			case 'rotation_time':
				$this->config[$name] = (int) $value;
				break;
			// arrays
			case 'config':
				$this->config[$name] = (array) $value;
			default:
				break;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Write the session
	 *
	 * This method is either called by every write operation, of at the
	 * end of processing a page request by the shutdown event handler
	 *
	 * @access	public	(otherwise the event handler can't access it!)
	 * @return  void
	 */
	public function write_session()
	{
		// cleanup any used flash variables
		$this->_cleanup_flash();

		// create the session if needed
		if (empty($this->keys))
		{
			$this->create();
		}

		// write the session cookie
		$this->_set_cookie($this->keys['session_id']);
	}

	// --------------------------------------------------------------------

	/**
	 * Gets the session cookie
	 *
	 * @access	private
	 * @return  array, cookie payload
	 */
	protected function _get_cookie()
	{
		// fetch the session cookie
		if ($cookie = Cookie::get($this->config['cookie_name'], false))
		{
			// fetch the payload
			$cookie = $this->_unserialize(Encrypt::decrypt($cookie));

			// validate the cookie
			if ( ! isset($cookie[0]) )
			{
				// not a valid cookie payload
			}
			elseif ($this->config['expiration_time'] && $cookie[0]['updated'] + $this->config['expiration_time'] <= $this->_gmttime())
			{
				// session has expired
			}
			elseif ($this->config['match_ip'] && $cookie[0]['ip_address'] !== Input::real_ip())
			{
				// IP address doesn't match
			}
			elseif ($this->config['match_ua'] && $cookie[0]['user_agent'] !== Input::user_agent())
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
		return array();
	}

	// --------------------------------------------------------------------

	/**
	 * Sets or creates the session cookie
	 *
	 * @access	private
	 * @return  void
	 */
	protected function _set_cookie($session_id = null, array $payload = array())
	{
		// do we have a valid session
		if (is_null($session_id))
		{
			// no, create one
			$this->keys['session_id']	= $this->_new_session_id();
			$this->keys['previous_id']	= '';
			$this->keys['ip_address']	= Input::real_ip();
			$this->keys['user_agent']	= Input::user_agent();
			$this->keys['created'] 		= $this->_gmttime();
		}
		else
		{
			// existing session. need to rotate the session id?
			if ($this->config['rotation_time'] && $this->keys['created'] + $this->config['rotation_time'] <= $this->_gmttime())
			{
				// create a new session id, and update the create timestamp
				$this->keys['previous_id']	= $this->keys['session_id'];
				$this->keys['session_id']	= $this->_new_session_id();
				$this->keys['created'] 		= $this->_gmttime();
			}
		}
		// record the last update time of the session
		$this->keys['updated'] = $this->_gmttime();

		// add the session keys to the payload
		array_unshift($payload, $this->keys);

		// encrypt the payload
		$payload = Encrypt::encrypt($this->_serialize($payload));
		if (strlen($payload) > 4000)
		{
			throw new Fuel_Exception('FuelPHP is configured to use session cookies, but the session data exceeds 4Kb. Use a different session type.');
		}

		// write the session cookie
		Cookie::set($this->config['cookie_name'], $payload, $this->config['expiration_time']);
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
	 * generates a GMT timestamp
	 *
	 * @access	private
	 * @return  void
	 */
	protected function _gmttime()
	{
		$now = time();
		return mktime(gmdate("H", $now), gmdate("i", $now), gmdate("s", $now), gmdate("m", $now), gmdate("d", $now), gmdate("Y", $now));
	}

}

/* End of file driver.php */
