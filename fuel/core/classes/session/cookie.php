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

namespace Fuel;

// --------------------------------------------------------------------

class Session_Cookie extends Session_Driver {

	// --------------------------------------------------------------------

	/**
	 * driver initialisation
	 *
	 * @access	public
	 * @return	void
	 */
	public function init()
	{
		// generic driver initialisation
		parent::init();

		// check for required config values
		$this->config['cookie_name'] = $this->validate_config('cookie_name', isset($this->config['cookie_name']) ? $this->config['cookie_name'] : 'fuelcid');
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
		// create a new session
		$this->keys['session_id']	= $this->_new_session_id();
		$this->keys['ip_address']	= Input::real_ip();
		$this->keys['user_agent']	= Input::user_agent();
		$this->keys['created'] 		= $this->time->get_timestamp();
		$this->keys['updated'] 		= $this->keys['created'];
		$this->keys['payload'] 		= '';

		// and set the session cookie
		$this->_set_cookie();
	}

	// --------------------------------------------------------------------

	/**
	 * read the session
	 *
	 * @access	public
	 * @param	boolean, set to true if we want to force a new session to be created
	 * @return	void
	 */
	public function read($force = false)
	{
		// get the session cookie
		$payload = $this->_get_cookie();

		// if no session cookie was present, create it
		if ($payload === false or $force)
		{
			$this->create();
		}

		if (isset($payload[0])) $this->data = $payload[0];
		if (isset($payload[1])) $this->flash = $payload[1];
	}

	// --------------------------------------------------------------------

	/**
	 * write the current session
	 *
	 * @access	public
	 * @return	void
	 */
	public function write()
	{
		// do we have something to write?
		if ( ! empty($this->keys))
		{
			// rotate the session id if needed
			$this->rotate(false);

			// then update the cookie
			$this->_set_cookie(array($this->data, $this->flash));
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
		// do we have something to destroy?
		if ( ! empty($this->keys))
		{
			// delete the session cookie
			Cookie::delete($this->config['cookie_name']);
		}

		// reset the stored session data
		$this->keys = $this->flash = $this->data = array();
	}

	// --------------------------------------------------------------------

	/**
	 * validate a driver config value
	 *
	 * @param	string	name of the config variable to validate
	 * @param	mixed	value
	 * @access	public
	 * @return  mixed
	 */
	public function validate_config($name, $value)
	{
		switch ($name)
		{
			case 'cookie_name':
				if ( empty($value) OR ! is_string($value))
				{
					$value = 'fuelcid';
				}
			break;

			default:
			break;
		}

		// return the validated value
		return $value;
	}
}

/* End of file cookie.php */
