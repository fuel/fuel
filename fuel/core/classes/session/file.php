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

class Session_File extends Session_Driver {

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
		$this->config['cookie_name'] = $this->validate_config('cookie_name', isset($this->config['cookie_name'])
				? $this->config['cookie_name'] : 'fuelfid');
		$this->config['path'] = $this->validate_config('path', $this->config['path']);
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

		// create the session record
		$this->_write_file($this->keys['session_id'], serialize(array()));

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
		$payload = $this->_read_file($this->keys['session_id']);

		if ($payload === false)
		{
			// try to find the previous one
			$payload = $this->_read_file($this->keys['previous_id']);

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
			$payload = $this->_read_file($payload['rotated_session_id']);
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
			$this->_write_file($this->keys['session_id'], $payload);

			// was the session id rotated?
			if ( isset($this->keys['previous_id']) && $this->keys['previous_id'] != $this->keys['session_id'])
			{
				// point the old session file to the new one, we don't want to lose the session
				$payload = $this->_serialize(array('rotated_session_id' => $this->keys['session_id']));
				$this->_write_file($this->keys['previous_id'], $payload);
			}

			$this->_set_cookie();

			// do some garbage collection
			if (mt_rand(0,100) < $this->config['gc_probability'])
			{
				if ($handle = opendir($this->config['path']))
				{
					$expire = $this->time->get_timestamp() - $this->config['expiration_time'];

					while (($file = readdir($handle)) !== false)
					{
						if (filetype($this->config['path'] . $file) == 'file' &&
							strpos($file, $this->config['cookie_name'].'_') === 0 &&
							filemtime($this->config['path'] . $file) < $expire)
						{
							@unlink($this->config['path'] . $file);
						}
					}
					closedir($handle);
				}
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
		if ( ! empty($this->keys))
		{
			// delete the session file
			$file = $this->config['path'].$this->config['cookie_name'].'_'.$this->keys['session_id'];
			if (file_exists($file))
			{
				unlink($file);
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
					$value = 'fuelfid';
				}
			break;

			case 'path':
				// do we have a path?
				if ( empty($value) OR ! is_dir($value))
				{
					throw new Exception('You have specify a valid path to store the session data files.');
				}
				// and can we write to it?
				if ( ! is_writable($value))
				{
					throw new Exception('The webserver doesn\'t have write access to the path to store the session data files.');
				}
				// update the path, and add the trailing slash
				$value = realpath($value).'/';
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
	 * Writes the session file
	 *
	 * @access	private
	 * @return  boolean, true if it was an existing session, false if not
	 */
	protected function _write_file($session_id, $payload)
	{
		// create the session file
		$file = $this->config['path'].$this->config['cookie_name'].'_'.$session_id;
		$exists = file_exists($file);
		$handle = fopen($file,'c');
		if ($handle)
		{
			// wait for a lock
			while(!flock($handle, LOCK_EX));

			// erase existing contents
			ftruncate($handle, 0);

			// write the session data
			fwrite($handle, $payload);

			//release the lock
			flock($handle, LOCK_UN);

			// close the file
			fclose($handle);
		}

		return $exists;
	}

	// --------------------------------------------------------------------

	/**
	 * Reads the session file
	 *
	 * @access	private
	 * @return  mixed, the payload if the file exists, or false if not
	 */
	protected function _read_file($session_id)
	{
		$payload = false;

		$file = $this->config['path'].$this->config['cookie_name'].'_'.$session_id;
		if (file_exists($file))
		{
			$handle = fopen($file,'r');
			if ($handle)
			{
				// wait for a lock
				while(!flock($handle, LOCK_EX));

				// read the session data
				$payload = fread($handle, filesize($file));

				//release the lock
				flock($handle, LOCK_UN);

				// close the file
				fclose($handle);

			}
		}
		return $payload;
	}

}

/* End of file file.php */
