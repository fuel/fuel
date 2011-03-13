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

class Session_Redis extends Session_Driver {

	/**
	 * array of driver config defaults
	 */
	protected static $_defaults = array(
		'cookie_name'		=> 'fuelrid',				// name of the session cookie for redis based sessions
		'database'			=> 'default'				// name of the redis database to use (as configured in config/db.php)
	);

	/*
	 * @var	storage for the redis object
	 */
	protected $redis = false;

	// --------------------------------------------------------------------

	public function __construct($config = array())
	{
		// merge the driver config with the global config
		$this->config = array_merge($config, is_array($config['redis']) ? $config['redis'] : static::$_defaults);

		$this->config = $this->_validate_config($this->config);
	}

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

		if ($this->redis === false)
		{
			// get the redis database instance
			$this->redis = \Redis::instance($this->config['database']);
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
		// create a new session
		$this->keys['session_id']	= $this->_new_session_id();
		$this->keys['previous_id']	= $this->keys['session_id'];	// prevents errors if previous_id has a unique index
		$this->keys['ip_address']	= \Input::real_ip();
		$this->keys['user_agent']	= \Input::user_agent();
		$this->keys['created'] 		= $this->time->get_timestamp();
		$this->keys['updated'] 		= $this->keys['created'];

		// create the session record
		$this->_write_redis($this->keys['session_id'], serialize(array()));

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
		$payload = $this->_read_redis($this->keys['session_id']);

		if ($payload === false)
		{
			// try to find the previous one
			$payload = $this->_read_redis($this->keys['previous_id']);

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
			$payload = $this->_read_redis($payload['rotated_session_id']);
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
		// do we have something to write?
		if ( ! empty($this->keys))
		{
			parent::write();

			// rotate the session id if needed
			$this->rotate(false);

			// session payload
			$payload = $this->_serialize(array($this->data, $this->flash));

			// create the session file
			$this->_write_redis($this->keys['session_id'], $payload);

			// was the session id rotated?
			if ( isset($this->keys['previous_id']) && $this->keys['previous_id'] != $this->keys['session_id'])
			{
				// point the old session file to the new one, we don't want to lose the session
				$payload = $this->_serialize(array('rotated_session_id' => $this->keys['session_id']));
				$this->_write_redis($this->keys['previous_id'], $payload);
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
			// delete the key from the redis server
			$this->redis->del($this->keys['session_id']);
		}

		// reset the stored session data
		$this->keys = $this->flash = $this->data = array();
	}

	// --------------------------------------------------------------------

	/**
	 * Writes the redis entry
	 *
	 * @access	private
	 * @return  boolean, true if it was an existing session, false if not
	 */
	protected function _write_redis($session_id, $payload)
	{
		// session payload
		$payload = $this->_serialize(array($this->data, $this->flash));

		// write it to the redis server
		$this->redis->set($this->keys['session_id'], $payload);
		$this->redis->expire($this->keys['session_id'], $this->config['expiration_time']);
	}

	// --------------------------------------------------------------------

	/**
	 * Reads the redis entry
	 *
	 * @access	private
	 * @return  mixed, the payload if the file exists, or false if not
	 */
	protected function _read_redis($session_id)
	{
		// fetch the session data from the Memcached server
		return $this->redis->get($this->keys['session_id']);
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
				switch ($item)
				{
					case 'cookie_name':
						if ( empty($item) OR ! is_string($item))
						{
							$item = 'fuelrid';
						}
					break;

					case 'database':
						// do we have a servers config
						if ( empty($item) OR ! is_array($item))
						{
							$item = 'default';
						}
					break;

					default:
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

/* End of file redis.php */
