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

class Session_Memcached extends Session_Driver {

	/*
	 * @var	storage for the memcached object
	 */
	protected $memcached = false;

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

		if ($this->memcached === false)
		{
			// make sure we have memcached servers configured
			$this->config['servers'] = $this->validate_config('servers', isset($this->config['servers']) ? $this->config['servers'] : array());

			// do we have the PHP memcached extension available
			if ( ! class_exists('Memcached') )
			{
				throw new Exception('Memcached sessions are configured, but your PHP installation doesn\'t have the Memcached extension loaded.');
			}

			// instantiate the memcached object
			$this->memcached = new \Memcached();

			// add the configured servers
			$this->memcached->addServers($this->config['servers']);

			// check if we can connect to the server(s)
			if ($this->memcached->getVersion() === false)
			{
				throw new Exception('Memcached sessions are configured, but there is no connection possible. Check your configuration.');
			}
		}

		$this->config['cookie_name'] = $this->validate_config('cookie_name', isset($this->config['cookie_name'])
				? $this->config['cookie_name'] : 'fuelmid');
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
		$this->keys['previous_id']	= $this->keys['session_id'];	// prevents errors if previous_id has a unique index
		$this->keys['ip_address']	= Input::real_ip();
		$this->keys['user_agent']	= Input::user_agent();
		$this->keys['created'] 		= $this->time->get_timestamp();
		$this->keys['updated'] 		= $this->keys['created'];

		// create the session record
		$this->_write_memcached($this->keys['session_id'], serialize(array()));

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
		$cookie = $this->_get_cookie();

		// if no session cookie was present, create it
		if ($cookie === false or $force)
		{
			$this->create();
		}

		// read the session file
		$payload = $this->_read_memcached($this->keys['session_id']);

		if ($payload === false)
		{
			// try to find the previous one
			$payload = $this->_read_memcached($this->keys['previous_id']);

			if ($payload === false)
			{
				// cookie present, but session record missing. force creation of a new session
				$this->read(true);
				return;
			}
		}

		// unpack the payload
		$payload = $this->_unserialize($payload);

		// session referral?
		if (isset($payload['rotated_session_id']))
		{
			$payload = $this->_read_memcached($payload['rotated_session_id']);
			if ($payload === false)
			{
				// cookie present, but session record missing. force creation of a new session
				$this->read(true);
				return;
			}
			else
			{
				// update the session
				$this->keys['previous_id'] = $this->keys['session_id'];
				$this->keys['session_id'] = $payload['rotated_session_id'];

				// unpack the payload
				$payload = $this->_unserialize($payload);
			}
		}

		if (isset($payload[0])) $this->data = $payload[0];
		if (isset($payload[1])) $this->flash = $payload[1];
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
		// do we have something to write?
		if ( ! empty($this->keys))
		{
			// rotate the session id if needed
			$this->rotate(false);

			// session payload
			$payload = $this->_serialize(array($this->data, $this->flash));

			// create the session file
			$this->_write_memcached($this->keys['session_id'], $payload);

			// was the session id rotated?
			if ( isset($this->keys['previous_id']) && $this->keys['previous_id'] != $this->keys['session_id'])
			{
				// point the old session file to the new one, we don't want to lose the session
				$payload = $this->_serialize(array('rotated_session_id' => $this->keys['session_id']));
				$this->_write_memcached($this->keys['previous_id'], $payload);
			}

			$this->_set_cookie();
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
			// delete the key from the memcached server
			if ($this->memcached->delete($this->config['cookie_name'].'_'.$this->keys['session_id']) === false)
			{
				throw new Exception('Memcached returned error code "'.$this->memcached->getResultCode().'" on delete. Check your configuration.');
			}
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
					$value = 'fuelmid';
				}
			break;

			case 'servers':
				// do we have a servers config
				if ( empty($value) OR ! is_array($value))
				{
					$value = array('default' => array('host' => '127.0.0.1', 'port' => '11211'));
				}

				// validate the servers
				foreach ($value as $key => $server)
				{
					// do we have a host?
					if ( ! isset($server['host']) OR ! is_string($server['host']))
					{
						throw new Exception('Invalid Memcached server definition in the session configuration.');
					}
					// do we have a port number?
					if ( ! isset($server['port']) OR ! is_numeric($server['port']) OR $server['port'] < 1025 OR $server['port'] > 65535)
					{
						throw new Exception('Invalid Memcached server definition in the session configuration.');
					}
					// do we have a relative server weight?
					if ( ! isset($server['weight']) OR ! is_numeric($server['weight']) OR $server['weight'] < 0)
					{
						// set a default
						$value[$key]['weight'] = 0;
					}
				}
			break;

			default:
			break;
		}

		return $value;
	}

	/**
	 * Writes the memcached entry
	 *
	 * @access	private
	 * @return  boolean, true if it was an existing session, false if not
	 */
	protected function _write_memcached($session_id, $payload)
	{
		// session payload
		$payload = $this->_serialize(array($this->data, $this->flash));

		// write it to the memcached server
		if ($this->memcached->set($this->config['cookie_name'].'_'.$this->keys['session_id'], $payload, $this->config['expiration_time']) === false)
		{
			throw new Exception('Memcached returned error code "'.$this->memcached->getResultCode().'" on write. Check your configuration.');
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Reads the memcached entry
	 *
	 * @access	private
	 * @return  mixed, the payload if the file exists, or false if not
	 */
	protected function _read_memcached($session_id)
	{
		// fetch the session data from the Memcached server
		return $this->memcached->get($this->config['cookie_name'].'_'.$this->keys['session_id']);
	}

}

/* End of file memcached.php */
