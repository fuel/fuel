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

class Session_Db extends Session_Driver {

	/*
	 * @var	session database result object
	 */
	protected $record = null;

	// --------------------------------------------------------------------

	/**
	 * database driver initialisation
	 *
	 * @access	public
	 * @return	void
	 */
	public function init()
	{
		// generic driver initialisation
		parent::init();

		// get the active database if needed
		if ( ! isset($this->config['database']))
		{
			Config::load('db', true);
			$this->config['database'] = Config::get('db.active');
		}

		// check for required config values
		$this->config['cookie_name'] = $this->validate_config('cookie_name', isset($this->config['cookie_name'])
				? $this->config['cookie_name'] : 'fueldid');
		$this->config['database'] = $this->validate_config('database', $this->config['database']);
		$this->config['table'] = $this->validate_config('table', isset($this->config['table']) ? $this->config['table'] : null);
		$this->config['gc_probability'] = $this->validate_config('gc_probability', isset($this->config['gc_probability'])
				? $this->config['gc_probability'] : 5);
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
		$this->keys['payload'] 		= '';

		// create the session record
		$result = DB::insert($this->config['table'], array_keys($this->keys))->values($this->keys)->execute($this->config['database']);

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

		// read the session record
		$this->record = DB::select()->where('session_id', '=', $this->keys['session_id'])->from($this->config['table'])->execute($this->config['database']);

		// record found?
		if ($this->record->count())
		{
			$payload = $this->_unserialize($this->record->get('payload'));
		}
		else
		{
			// try to find the session on previous id
			$this->record = DB::select()->where('previous_id', '=', $this->keys['session_id'])->from($this->config['table'])->execute($this->config['database']);

			// record found?
			if ($this->record->count())
			{
				$payload = $this->_unserialize($this->record->get('payload'));
			}
			else
			{
				// cookie present, but session record missing. force creation of a new session
				$this->read(true);
				return;
			}
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
		if ( ! empty($this->keys) and ! empty($this->record))
		{
			// rotate the session id if needed
			$this->rotate(false);

			// create the session record, and add the session payload
			$session = $this->keys;
			$session['payload'] = $this->_serialize(array($this->data, $this->flash));

			// update the database
			$result = DB::update($this->config['table'])->set($session)->where('session_id', '=', $this->record->get('session_id'))->execute($this->config['database']);

			// update went well?
			if ($result)
			{
				// then update the cookie
				$this->_set_cookie();
			}
			else
			{
				Log::error('Session update failed, session record could not be found. Concurrency issue?');
			}

			// do some garbage collection
			if (mt_rand(0,100) < $this->config['gc_probability'])
			{
				$expired = $this->time->get_timestamp() - $this->config['expiration_time'];
				$result = DB::delete($this->config['table'])->where('updated', '<', $expired)->execute($this->config['database']);
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
		// do we have something to destroy?
		if ( ! empty($this->keys) and ! empty($this->record))
		{
			// delete the session record
			$result = DB::delete($this->config['table'])->where('session_id', '=', $this->keys['session_id'])->execute($this->config['database']);
		}

		// reset the stored session data
		$this->record = null;
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
					$value = 'fueldid';
				}
			break;

			case 'database':
				// do we have a database?
				if ( empty($value) OR ! is_string($value))
				{
					throw new Exception('You have specify a database to use database backed sessions.');
				}
			break;

			case 'table':
				// and a table name?
				if ( empty($value) OR ! is_string($value))
				{
					throw new Exception('You have specify a database table name to use database backed sessions.');
				}
			break;

			case 'gc_probability':
				// do we have a path?
				if ( ! is_numeric($value) OR $value < 0 OR $value > 100)
				{
					// default value: 5%
					$value = 5;
				}
			break;

			default:
			break;
		}

		// return the validated value
		return $value;
	}
}

/* End of file db.php */
