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

class Session_Db_Driver extends Session_Driver {

	// --------------------------------------------------------------------

	/**
	 * driver initialisation
	 *
	 * @access	public
	 * @return	void
	 */
	public function init()
	{
		// get the active database if needed
		if ( ! isset($this->config['database']))
		{
			Config::load('db', true);
			$this->config['database'] = Config::get('db.active');
		}

		// check for required config values
		$this->config['database'] = $this->validate_config('database', $this->config['database']);
		$this->config['table'] = $this->validate_config('table', isset($this->config['table']) ? $this->config['table'] : null);
		$this->config['gc_probability'] = $this->validate_config('gc_probability', isset($this->config['gc_probability']) ? $this->config['gc_probability'] : 5);
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

		// do some garbage collection
		srand(time());
		if ((rand() % 100) < $this->config['gc_probability'])
		{
			$expired = $this->_gmttime() - $this->config['expiration_time'];
			$result = DB::delete($this->config['table'])->where('updated', '<', $expired)->execute($this->config['database']);
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
		// delete the session record
		$result = DB::delete($this->config['table'])->where('session_id', '=', $this->keys['session_id'])->execute($this->config['database']);

		// delete the cookie, reset all session variables
		parent::destroy();
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

	// --------------------------------------------------------------------

	/**
	 * Sets or creates the session cookie
	 *
	 * the db driver stores data and flash in a database table
	 *
	 * @access	private
	 * @return  void
	 */
	protected function _set_cookie($session_id = NULL)
	{
		// create the session cookie
		parent::_set_cookie($session_id);

		// create the initial session record
		$session = array(
			'session_id' => $this->keys['session_id'],
			'previous_id' => ( ! is_null($session_id) && $session_id != $this->keys['session_id']) ? $session_id : $this->keys['session_id'],
			'ip_address' => Input::real_ip(),
			'user_agent' => Input::user_agent(),
			'updated' => $this->_gmttime(),
			'payload' => $this->_serialize(array($this->data, $this->flash))
		);

		if (is_null($session_id))
		{
			// set the create timestamp
			$session['created'] = $this->_gmttime();

			// insert it into the database
			$result = DB::insert($this->config['table'], array_keys($session))->values($session)->execute($this->config['database']);
		}
		else
		{
			// was the session id rotated?
			if ( ! is_null($session_id) && $session_id != $this->keys['session_id'])
			{
				// if so, save the previous id
				$session['previous_id'] = $session_id;
			}
			// update the database
			$result = DB::update($this->config['table'])->set($session)->where('session_id', '=', $session_id)->execute($this->config['database']);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Gets the session cookie
	 *
	 * the db driver stores data and flash in a database table
	 *
	 * @access	private
	 * @return  boolean, true if found, false if not
	 */
	protected function _get_cookie()
	{
		// fetch the session cookie
		parent::_get_cookie();

		// read the session record
		if (isset($this->keys['session_id']))
		{
			$result = DB::select()->where('session_id', '=', $this->keys['session_id'])->from($this->config['table'])->execute($this->config['database']);

			// record found?
			if (count($result))
			{
				$payload = $this->_unserialize($result->get('payload'));
			}
			else
			{
				// try to find the session on previous id
				$result = DB::select()->where('previous_id', '=', $this->keys['session_id'])->from($this->config['table'])->execute($this->config['database']);

				// record found?
				if (count($result))
				{
					$payload = $this->_unserialize($result->get('payload'));
				}
				else
				{
					$this->keys = NULL;
				}
			}
		}

		if (isset($payload[0])) $this->data = $payload[0];
		if (isset($payload[1])) $this->flash = $payload[1];

		return ! empty($this->keys);
	}

}

/* End of file driver.php */
