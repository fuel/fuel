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

class Fuel_Session_Memcached_Driver extends Session_Driver {

	/*
	 * @var	storage for the memcached object
	 */
	protected $memcached = FALSE;

	// --------------------------------------------------------------------

	/**
	 * driver initialisation
	 *
	 * @access	public
	 * @return	void
	 */
	public function _init()
	{
		if ($this->memcached === false)
		{
			// do we have the PHP memcached extension available
			if ( ! class_exists('Memcached') )
			{
				throw new Fuel_Session_Exception('Memcached sessions are configured, but your PHP installation doesn\'t have the Memcached extension loaded.');
			}

			// instantiate the memcached object
			$this->memcached = new Memcached();

			// add the configured servers
			$this->memcached->addServers($this->config['config']['servers']);

			// check if we can connect to the server(s)
			if ($this->memcached->getVersion() === false)
			{
				throw new Fuel_Session_Exception('Memcached sessions are configured, but there is no connection possible. Check your configuration.');
			}
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
		// delete the key from the memcached server
		if ($this->memcached->deleteByKey($this->config['cookie_name'], $this->keys['session_id']) === false)
		{
			throw new Fuel_Session_Exception('Memcached returned error code "'.$this->memcached->getResultCode().'" on delete. Check your configuration.');
		}

		// delete the cookie, reset all session variables
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
		// driver specific config?
		if ($name == 'config')
		{
			// do we have a servers config
			if ( ! isset($value['servers']) OR ! is_array($value['servers']))
			{
				$value['servers'] = array('default' => array('host' => '127.0.0.1', 'port' => '11211'));
			}

			// validate the servers
			foreach ($value['servers'] as $key => $server)
			{
				// do we have a host?
				if ( ! isset($server['host']) OR ! is_string($server['host']))
				{
					throw new Fuel_Session_Exception('Invalid Memcached server definition in the session configuration.');
				}
				// do we have a port number?
				if ( ! isset($server['port']) OR ! is_numeric($server['port']) OR $server['port'] < 1025 OR $server['port'] > 65535)
				{
					throw new Fuel_Session_Exception('Invalid Memcached server definition in the session configuration.');
				}
				// do we have a relative server weight?
				if ( ! isset($server['weight']) OR ! is_numeric($server['weight']) OR $server['weight'] < 0)
				{
					// set a default
					$value['servers'][$key]['weight'] = 0;
				}
			}
		}

		// store the config value
		parent::set_config($name, $value);
	}

	// --------------------------------------------------------------------

	/**
	 * Sets or creates the session cookie
	 *
	 * the file driver stores data and flash in a separate session file
	 *
	 * @access	private
	 * @return  void
	 */
	protected function _set_cookie($session_id = NULL)
	{
		parent::_set_cookie($session_id);

		// session payload
		$payload = $this->_serialize(array($this->data, $this->flash));

		// write it to the memcached server
		$expiration = $this->config['expiration_time'] == 0 ? 86400 * 30 : $this->config['expiration_time'] + 60;
		if ($this->memcached->setByKey($this->config['cookie_name'], $this->keys['session_id'], $payload, $expiration) === false)
		{
			throw new Fuel_Session_Exception('Memcached returned error code "'.$this->memcached->getResultCode().'" on write. Check your configuration.');
		}
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
		// fetch the session cookie
		parent::_get_cookie();

		// read the session file
		if ( ! empty($this->keys))
		{
			// fetch the session data from the Memcached server
			$payload = $this->memcached->getByKey($this->config['cookie_name'], $this->keys['session_id']);

			if ($payload === false)
			{
				throw new Fuel_Session_Exception('Memcached returned error code "'.$this->memcached->getResultCode().'" on read. Check your configuration.');
			}
			else
			{
				$payload = $this->_unserialize($payload);
			}

			if (isset($payload[0])) $this->data = $payload[0];
			if (isset($payload[1])) $this->flash = $payload[1];
		}

		return ! empty($this->keys);
	}

}

/* End of file driver.php */
