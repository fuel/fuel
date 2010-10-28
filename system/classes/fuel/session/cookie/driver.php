
<?php defined('SYSPATH') or die('No direct script access.');
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

// --------------------------------------------------------------------

/**
 * Session Class
 *
 * @package		Fuel
 * @category	Sessions
 * @author		Harro "WanWizard" Verton
 */

class Fuel_Session_Cookie_Driver extends Session_Driver {

	protected $initialized = FALSE;

	protected $config = array();

	protected $keys = array();

	protected $data = array();

	protected $flash = array();

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
		return isset($this->data[$name]) ? $this->data[$name] : FALSE;
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
		$this->flash[$name] = array('state' => 'new', 'value' => $value);
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
		if (isset($this->flash[$name]))
		{
			$this->flash[$name]['state'] = 'old';
			return $this->flash[$name]['value'];
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
		if (isset($this->flash[$name]))
		{
			$this->flash[$name]['state'] = 'new';
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
		if (isset($this->flash[$name]))
		{
			unset($this->flash[$name]);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * create a new session
	 *
	 * @access	public
	 * @return	void
	 */
	public function create()
	{
		$this->_initialize();
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
		// initialize the session object
		$this->_initialize();

		// fetch the session cookie
		if( ! $this->_get_cookie())
		{
			$this->create();
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
		$this->_initialize();
		$this->_cleanup_flash();
		$this->_set_cookie($this->keys['session_id']);
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
		$this->_initialize();
		Cookie::delete($this->config['cookie_name']);
		$this->keys = array();
		$this->data = array();
		$this->flash = array();
	}

	// --------------------------------------------------------------------

	/**
	 * Sets and validates the session configuration
	 *
	 * @access	private
	 * @return  void
	 */
	private function _initialize()
	{
		if (empty($this->initialized))
		{
			// load the session configuration
			Config::load('session', 'session');
			$this->config = Config::get('session');

			// validate the config
			if ( ! isset($this->config['type']) OR ! in_array($this->config['type'], $this->valid_storage))
			{
				throw new Fuel_Exception('You have specified an invalid session storage system.');
			}

			// set the config
			$this->config['expiration'] = isset($this->config['expiration']) ? (int) $this->config['expiration'] : 0;
			$this->config['match_ip'] = isset($this->config['match_ip']) ? (bool) $this->config['match_ip'] : TRUE;
			$this->config['match_ua'] = isset($this->config['match_ua']) ? (bool) $this->config['match_ua'] : TRUE;
			$this->config['cookie_name'] = isset($this->config['cookie_name']) ? (string) $this->config['cookie_name'] : 'fuelsession';
			$this->config['cookie_domain'] = isset($this->config['cookie_domain']) ? (string) $this->config['cookie_domain'] : '';
			$this->config['cookie_path'] = isset($this->config['cookie_path']) ? (string) $this->config['cookie_path'] : '/';
			$this->config['rotation'] = isset($this->config['rotation']) ? (int) $this->config['rotation'] : 0;
			$this->config['flash_id'] = isset($this->config['flash_id']) ? (string) $this->config['flash_id'] : 'flash';

			// we're done
			$this->initialized = TRUE;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * removes flash variables marked as old
	 *
	 * @access	private
	 * @return  void
	 */
	private function _cleanup_flash()
	{
		foreach($this->flash as $key => $value)
		{
			if ($value['state'] == 'old')
			{
				unset($this->flash[$key]);
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Sets or creates the session cookie
	 *
	 * @access	private
	 * @return  void
	 */
	private function _set_cookie($session_id = NULL)
	{
		// do we have a valid session
		if (is_null($session_id))
		{
			// no, create one
			$this->keys['session_id']	= $this->_new_session_id();
			$this->keys['ip_address']	= Input::real_ip();
			$this->keys['user_agent']	= Input::user_agent();
			$this->keys['created'] 		= $this->_gmttime();
		}
		else
		{
			// existing session. need to rotate the session id?
			if ($this->config['rotation'] && $this->keys['created'] + $this->config['rotation'] <= $this->_gmttime())
			{
				// create a new session id, and update the create timestamp
				$this->keys['session_id']	= $this->_new_session_id();
				$this->keys['created'] 		= $this->_gmttime();
			}
		}
		// record the last update time of the session
		$this->keys['updated'] = $this->_gmttime();

		// update the session
		$payload = array($this->keys, $this->data, $this->flash);

		// write the session cookie
		Cookie::set($this->config['cookie_name'], $this->_serialize($payload),$this->config['expiration']);
	}

	// --------------------------------------------------------------------

	/**
	 * Gets the session cookie
	 *
	 * @access	private
	 * @return  boolean, true if found, false if not
	 */
	private function _get_cookie()
	{
		// fetch the session cookie
		if ($cookie = Cookie::get($this->config['cookie_name'], FALSE))
		{
			// fetch the payload
			$payload = $this->_unserialize($cookie);

			// retrieve the data from the cookie
			$this->keys = $payload[0];
			$this->data = $payload[1];
			$this->flash = $payload[1];
		}

		return ! empty($this->keys);

	}

	// --------------------------------------------------------------------

	/**
	 * generate a new session id
	 *
	 * @access	private
	 * @return  void
	 */
	private function _new_session_id()
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
	private function _serialize($data)
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
	private function _unserialize($data)
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
	private function _gmttime()
	{
		$now = time();
		return mktime(gmdate("H", $now), gmdate("i", $now), gmdate("s", $now), gmdate("m", $now), gmdate("d", $now), gmdate("Y", $now));
	}

}

/* End of file session.php */
