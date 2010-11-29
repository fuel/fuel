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
	 * Sets or creates the session cookie
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
			// cookie driver doesn't have any special config values
			default:
				break;
		}

		// return the validated value
		return $value;
	}
}

/* End of file driver.php */
