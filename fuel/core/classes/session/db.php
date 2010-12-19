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

namespace Fuel\Core;

use Fuel\App as App;

// --------------------------------------------------------------------

class Session_Db extends Session_Driver {

	/*
	 * @var	session database result object
	 */
	protected $record = null;

	/**
	 * array of driver config defaults
	 */
	protected static $_defaults = array(
		'cookie_name'		=> 'fueldid',				// name of the session cookie for database based sessions
		'table'				=> 'sessions',				// name of the sessions table
		'gc_probability'	=> 5						// probability % (between 0 and 100) for garbage collection
	);

	// --------------------------------------------------------------------

	public function __construct($config = array())
	{
		// merge the driver config with the global config
		$this->config = array_merge($config, is_array($config['db']) ? $config['db'] : static::$_defaults);

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
		$this->keys['previous_id']	= $this->keys['session_id'];	// prevents errors if previous_id has a unique index
		$this->keys['ip_address']	= App\Input::real_ip();
		$this->keys['user_agent']	= App\Input::user_agent();
		$this->keys['created'] 		= $this->time->get_timestamp();
		$this->keys['updated'] 		= $this->keys['created'];
		$this->keys['payload'] 		= '';

		// create the session record
		$result = App\DB::insert($this->config['table'], array_keys($this->keys))->values($this->keys)->execute($this->config['database']);

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
		$this->record = App\DB::select()->where('session_id', '=', $this->keys['session_id'])->from($this->config['table'])->execute($this->config['database']);

		// record found?
		if ($this->record->count())
		{
			$payload = $this->_unserialize($this->record->get('payload'));
		}
		else
		{
			// try to find the session on previous id
			$this->record = App\DB::select()->where('previous_id', '=', $this->keys['session_id'])->from($this->config['table'])->execute($this->config['database']);

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
			$result = App\DB::update($this->config['table'])->set($session)->where('session_id', '=', $this->record->get('session_id'))->execute($this->config['database']);

			// update went well?
			if ($result)
			{
				// then update the cookie
				$this->_set_cookie();
			}
			else
			{
				logger(Fuel::L_ERROR, 'Session update failed, session record could not be found. Concurrency issue?');
			}

			// do some garbage collection
			if (mt_rand(0,100) < $this->config['gc_probability'])
			{
				$expired = $this->time->get_timestamp() - $this->config['expiration_time'];
				$result = App\DB::delete($this->config['table'])->where('updated', '<', $expired)->execute($this->config['database']);
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
			$result = App\DB::delete($this->config['table'])->where('session_id', '=', $this->keys['session_id'])->execute($this->config['database']);
		}

		// reset the stored session data
		$this->record = null;
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
							$item = 'fueldid';
						}
					break;

					case 'database':
						// do we have a database?
						if ( empty($item) OR ! is_string($item))
						{
							App\Config::load('db', true);
							$item = App\Config::get('db.active', false);
						}
						if ($item === false)
						{
							throw new App\Exception('You have specify a database to use database backed sessions.');
						}
					break;

					case 'table':
						// and a table name?
						if ( empty($item) OR ! is_string($item))
						{
							throw new App\Exception('You have specify a database table name to use database backed sessions.');
						}
					break;

					case 'gc_probability':
						// do we have a path?
						if ( ! is_numeric($item) OR $item < 0 OR $item > 100)
						{
							// default value: 5%
							$item = 5;
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

/* End of file db.php */
