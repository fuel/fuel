<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2011 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Core;



// --------------------------------------------------------------------

class Session_Cookie extends Session_Driver {

	/**
	 * array of driver config defaults
	 */
	protected static $_defaults = array(
		'cookie_name'		=> 'fuelcid',
	);

	// --------------------------------------------------------------------

	public function __construct($config = array())
	{
		// merge the driver config with the global config
		$this->config = array_merge($config, is_array($config['cookie']) ? $config['cookie'] : static::$_defaults);

		$this->config = $this->_validate_config($this->config);
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
		$this->keys['ip_address']	= \Input::real_ip();
		$this->keys['user_agent']	= \Input::user_agent();
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

		parent::read();
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
		parent::write();

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
			\Cookie::delete($this->config['cookie_name']);
		}

		// reset the stored session data
		$this->keys = $this->flash = $this->data = array();
	}

	// --------------------------------------------------------------------

	/**
	 * validate a driver config value
	 *
	 * @param	array	array with configuration values
	 * @access	public
	 * @return  array	validated and consolidated config
	 */
	public function _validate_config($config)
	{
		$validated = array();

		foreach ($config as $name => $item)
		{
			// filter out any driver config
			if (!is_array($item))
			{
				switch ($name)
				{
					case 'cookie_name':
						if ( empty($item) OR ! is_string($item))
						{
							$item = 'fuelcid';
						}
					break;

					default:
						// no config item for this driver
					break;
				}

				// global config, was validated in the driver
				$validated[$name] = $item;
			}
		}

		// validate all global settings as well
		return parent::_validate_config($validated);
	}
}

/* End of file cookie.php */
