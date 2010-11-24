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

class Session_File_Driver extends Session_Driver {

	// --------------------------------------------------------------------

	/**
	 * driver initialisation
	 *
	 * @access	public
	 * @return	void
	 */
	public function init()
	{
		// check for required config values
		$this->config['path'] = $this->validate_config('path', $this->config['path']);
		$this->config['gc_probability'] = $this->validate_config('gc_probability', isset($this->config['gc_probability']) ? $this->config['gc_probability'] : 5);
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
		// delete the session file
		$file = $this->config['path'].$this->keys['session_id'];
		if (file_exists($file))
		{
			unlink($file);
		}

		// delete the cookie, reset all session variables
		parent::destroy();
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
			if ($handle = opendir($this->config['path']))
			{
				$expire = $this->_gmttime() - $this->config['expiration_time'];

				while (($file = readdir($handle)) !== false)
				{
					if (filetype($this->config['path'] . $file) == 'file' &&
						strpos($file, $this->config['cookie_name']) === 0 &&
						filemtime($this->config['path'] . $file) < $expire)
					{
						@unlink($this->config['path'] . $file);
					}
				}
				closedir($handle);
			}
		}
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
	 * Sets or creates the session cookie
	 *
	 * the file driver stores data and flash in a separate session file
	 *
	 * @access	private
	 * @return  void
	 */
	protected function _set_cookie($session_id = NULL)
	{
		// create the session cookie
		parent::_set_cookie($session_id);

		// session payload
		$payload = $this->_serialize(array($this->data, $this->flash));

		// create the session file
		$this->_write_file($this->keys['session_id'], $payload);

		// was the session id rotated?
		if ( ! is_null($session_id) && $session_id != $this->keys['session_id'])
		{
			// point the old session file to the new one, we don't want to lose the session
			$payload = $this->_serialize(array('rotated_session_id' => $this->keys['session_id']));
			$this->_write_file($session_id, $payload);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Gets the session cookie
	 *
	 * the file driver stores data and flash in a separate session file
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
			// read the session file
			$payload = $this->_read_file($this->keys['session_id']);

			if ($payload)
			{
				$payload = $this->_unserialize($payload);

				// check for rotated session id's
				if (isset($payload['rotated_session_id']))
				{
					// get the session data using the rotated session id
					$payload = $this->_read_file($payload['rotated_session_id']);
					$payload = $this->_unserialize($payload);
				}

				if (isset($payload[0])) $this->data = $payload[0];
				if (isset($payload[1])) $this->flash = $payload[1];
			}
		}

		return ! empty($this->keys);
	}

	// --------------------------------------------------------------------

	/**
	 * Writes the session file
	 *
	 * @access	private
	 * @return  boolean, true if found, false if not
	 */
	protected function _write_file($session_id, $payload)
	{
		// create the session file
		$file = $this->config['path'].$this->config['cookie_name'].'_'.$session_id;
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

/* End of file driver.php */
