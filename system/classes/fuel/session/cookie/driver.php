<?php defined('SYSPATH') or die('No direct script access.');
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

class Fuel_Session_Cookie_Driver extends Session_Driver {

	/**
	 * create a new session
	 *
	 * @access	public
	 * @return	void
	 */
	public function create()
	{
		parent::create();
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
		parent::read();
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
		parent::write();
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
		parent::destroy();
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
		// store the config value
		parent::set_config($name, $value);

		// driver specific config?
		if ($name == 'config')
		{
			// cookie driver doesn't have any specific config values
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Sets or creates the session cookie
	 *
	 * the cookie driver stores data and flash in the cookie payload
	 *
	 * @access	private
	 * @return  void
	 */
	protected function _set_cookie($session_id = NULL)
	{
		parent::_set_cookie($session_id, array($this->data, $this->flash));
	}

	// --------------------------------------------------------------------

	/**
	 * Gets the session cookie
	 *
	 * the cookie driver stores data and flash in the cookie payload
	 *
	 * @access	private
	 * @return  boolean, true if found, false if not
	 */
	protected function _get_cookie()
	{
		// cookie already loaded?
		if ( empty($this->keys) )
		{
			// fetch the session cookie
			$payload = parent::_get_cookie();

			// retrieve our payload from the cookie
			if ( ! empty($this->keys) )
			{
				if (isset($payload[0])) $this->data = $payload[0];
				if (isset($payload[1])) $this->flash = $payload[1];
			}
		}

		return ! empty($this->keys);
	}

}

/* End of file session.php */
