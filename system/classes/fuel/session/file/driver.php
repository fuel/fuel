<?php defined('SYSPATH') or die('No direct script access.');
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

class Fuel_Session_File_Driver extends Session_Driver {

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
		$file = $this->config['config']['path'].$this->keys['session_id'];
		if (file_exists($file))
		{
			unlink($file);
		}

		// delete the cookie, reset all session variables
		parent::destroy();
	}

	// --------------------------------------------------------------------

	/**
	 * destroy the current session
	 *
	 * @access	public
	 * @return	void
	 */
	public function write()
	{
		parent::write();

		// do some garbage collection
		srand(time());
		if ((rand() % 100) < $this->config['config']['gc_probability'])
		{
			if ($handle = opendir($this->config['config']['path']))
			{
				$expire = $this->_gmttime() - $this->config['expiration_time'];

				while (($file = readdir($handle)) !== false)
				{
					if (filetype($this->config['config']['path'] . $file) == 'file')
					{
						if (filemtime($this->config['config']['path'] . $file) < $expire)
						{
							@unlink($this->config['config']['path'] . $file);
						}
					}
				}
				closedir($handle);
			}
		}
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
			// do we have a path?
			if ( ! isset($value['path']) OR ! is_dir($value['path']))
			{
				throw new Fuel_Exception('You have specify a path to store the session data files.');
			}
			// and can we write to it?
			if ( ! is_writable($value['path']))
			{
				throw new Fuel_Exception('The webserver doesn\'t have write access to the path to store the session data files.');
			}
			// update the path, and add the trailing slash
			$value['path'] = realpath($value['path']).'/';

			// do we have a path?
			if ( ! isset($value['gc_probability']) OR ! is_numeric($value['gc_probability']) OR $value['gc_probability'] < 0 OR $value['gc_probability'] > 100)
			{
				// default value: 5%
				$value['gc_probability'] = 5;
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

		// create the session file
		$file = $this->config['config']['path'].$this->keys['session_id'];
		$handle = fopen($file,'c');
		if ($handle)
		{
			// wait for a lock
			while(!flock($handle, LOCK_EX));

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
			// read the session file
			$file = $this->config['config']['path'].$this->keys['session_id'];
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

					$payload = $this->_unserialize($payload);

					if (isset($payload[0])) $this->data = $payload[0];
					if (isset($payload[1])) $this->flash = $payload[1];
				}
			}
		}

		return ! empty($this->keys);
	}

}

/* End of file session.php */
