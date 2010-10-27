<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Fuel Development Team
 * @license		MIT License
 * @copyright	2010 Dan Horrigan
 * @link		http://fuelphp.com
 */

// ------------------------------------------------------------------------

/**
 * FTP Class
 *
 * @package		Fuel
 * @category	Core
 * @author		Philip Sturgeon
 */
class Fuel_Ftp
{
	public static $initialized = FALSE;
	
	protected $_hostname		= '';
	protected $_username		= '';
	protected $_password		= '';
	protected $_port			= 21;
	protected $_passive		= TRUE;
	protected $_debug		= FALSE;
	protected $_conn_id		= FALSE;

	/**
	 * Returns a new Ftp object. If you do not define the "file" parameter,
	 *
	 *     $ftp = Ftp::factory('group');
	 *
	 * @param   string  Ftp filename
	 * @param   array   array of values
	 * @return  Ftp
	 */
	public static function factory($config = 'default')
	{
		return new Ftp($config);
	}

	/**
	 * Sets the initial Ftp filename and local data.
	 *
	 * @param   string  Ftp filename
	 * @param   array   array of values
	 * @return  void
	 */
	public function __construct($config = 'default')
	{
		if (empty(Ftp::$initialized))
		{
			Config::load('ftp', 'ftp');
		}

		// If it is a string we're looking at a predefined config group
		if (is_string($config))
		{
			$servers = Config::get('ftp.servers');

			// Check that it exists
			if ( ! isset($servers[$config]))
			{
				throw new Fuel_Exception('You have specified an invalid ftp connection group: '.$config);
			}

			// Now we have an array, we dont need the string any more
			$config = $servers[$config];
		}

		// Prep the hostname
		$this->_hostname = preg_replace('|.+?://|', '', $config['hostname']);
		$this->_username = $config['username'];
		$this->_password = $config['password'];
		$this->_port = ! empty($config['port']) ? (int) $config['port'] : 21;
		$this->_passive = (bool) $config['passive'];
		$this->_ssl_mode = (bool) $config['ssl_mode'];
		$this->_debug = (bool) $config['debug'];

		Ftp::$initialized = TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * FTP Connect
	 *
	 * @access	public
	 * @param	array	 the connection values
	 * @return	bool
	 */
	function connect()
	{
		if($this->_ssl_mode === TRUE)
		{
//			if( ! function_exists('ftp_ssl_connect'))
//			{
//				throw new Fuel_Exception('ftp_ssl_connect() is missing.');
//			}

			$this->_conn_id = @ftp_ssl_connect($this->_hostname, $this->_port);
		}
		
		else
		{
			$this->_conn_id = @ftp_connect($this->_hostname, $this->_port);
		}

		if ($this->_conn_id === FALSE)
		{
			if ($this->_debug == TRUE)
			{
				throw new Fuel_Exception('ftp_unable_to_connect');
			}
			return FALSE;
		}

		if ( ! $this->_login())
		{
			if ($this->_debug == TRUE)
			{
				throw new Fuel_Exception('ftp_unable_to_login');
			}
		}

		// Set passive mode if needed
		if ($this->_passive == TRUE)
		{
			ftp_pasv($this->_conn_id, TRUE);
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * FTP Login
	 *
	 * @access	private
	 * @return	bool
	 */
	function _login()
	{
		return ftp_login($this->_conn_id, $this->_username, $this->_password);
	}

	// --------------------------------------------------------------------

	/**
	 * Validates the connection ID
	 *
	 * @access	private
	 * @return	bool
	 */
	function _is_conn()
	{
		if ( ! is_resource($this->_conn_id))
		{
			if ($this->_debug == TRUE)
			{
				throw new Fuel_Exception('ftp_no_connection');
			}
			return FALSE;
		}
		return TRUE;
	}

	// --------------------------------------------------------------------


	/**
	 * Change directory
	 *
	 * The second parameter lets us momentarily turn off debugging so that
	 * this function can be used to test for the existence of a folder
	 * without throwing an error.  There's no FTP equivalent to is_dir()
	 * so we do it by trying to change to a particular directory.
	 * Internally, this parameter is only used by the "mirror" function below.
	 *
	 * @access	public
	 * @param	string
	 * @param	bool
	 * @return	bool
	 */
	function change_dir($path = '', $supress_debug = FALSE)
	{
		if ($path == '' OR ! $this->_is_conn())
		{
			return FALSE;
		}

		$result = @ftp_chdir($this->_conn_id, $path);

		if ($result === FALSE)
		{
			if ($this->_debug == TRUE AND $supress_debug == FALSE)
			{
				throw new Fuel_Exception('ftp_unable_to_change_dir');
			}
			return FALSE;
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Create a directory
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function mkdir($path = '', $permissions = NULL)
	{
		if ($path == '' OR ! $this->_is_conn())
		{
			return FALSE;
		}

		$result = ftp_mkdir($this->_conn_id, $path);

		if ($result === FALSE)
		{
			if ($this->_debug == TRUE)
			{
				throw new Fuel_Exception('ftp_unable_to_makdir');
			}
			return FALSE;
		}

		// Set file permissions if needed
		if ( ! is_null($permissions))
		{
			$this->chmod($path, (int)$permissions);
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Upload a file to the server
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	function upload($locpath, $rempath, $mode = 'auto', $permissions = NULL)
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		if ( ! file_exists($locpath))
		{
			throw new Fuel_Exception('ftp_no_source_file');
			return FALSE;
		}

		// Set the mode if not specified
		if ($mode == 'auto')
		{
			// Get the file extension so we can set the upload type
			$ext = pathinfo($locpath, PATHINFO_EXTENSION);
			$mode = $this->_settype($ext);
		}

		$mode = ($mode == 'ascii') ? FTP_ASCII : FTP_BINARY;

		$result = @ftp_put($this->_conn_id, $rempath, $locpath, $mode);

		if ($result === FALSE)
		{
			if ($this->_debug == TRUE)
			{
				throw new Fuel_Exception('ftp_unable_to_upload');
			}
			return FALSE;
		}

		// Set file permissions if needed
		if ( ! is_null($permissions))
		{
			$this->chmod($rempath, (int)$permissions);
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Download a file from a remote server to the local server
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	function download($rempath, $locpath, $mode = 'auto')
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		// Set the mode if not specified
		if ($mode == 'auto')
		{
			// Get the file extension so we can set the upload type
			$ext = pathinfo($rempath, PATHINFO_BASENAME);
			$mode = $this->_settype($ext);
		}

		$mode = ($mode == 'ascii') ? FTP_ASCII : FTP_BINARY;

		$result = @ftp_get($this->_conn_id, $locpath, $rempath, $mode);

		if ($result === FALSE)
		{
			if ($this->_debug === TRUE)
			{
				throw new Fuel_Exception('ftp_unable_to_download');
			}
			return FALSE;
		}

		return TRUE;
    }

	// --------------------------------------------------------------------

	/**
	 * Rename (or move) a file
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	bool
	 * @return	bool
	 */
	function rename($old_file, $new_file, $move = FALSE)
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		$result = @ftp_rename($this->_conn_id, $old_file, $new_file);

		if ($result === FALSE)
		{
			if ($this->_debug == TRUE)
			{
				$msg = ($move == FALSE) ? 'ftp_unable_to_rename' : 'ftp_unable_to_move';

				throw new Fuel_Exception($msg);
			}
			return FALSE;
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Move a file
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	function move($old_file, $new_file)
	{
		return $this->rename($old_file, $new_file, TRUE);
	}

	// --------------------------------------------------------------------

	/**
	 * Rename (or move) a file
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function delete_file($filepath)
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		$result = @ftp_delete($this->_conn_id, $filepath);

		if ($result === FALSE)
		{
			if ($this->_debug == TRUE)
			{
				throw new Fuel_Exception('ftp_unable_to_delete');
			}
			return FALSE;
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete a folder and recursively delete everything (including sub-folders)
	 * containted within it.
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function delete_dir($filepath)
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		// Add a trailing slash to the file path if needed
		$filepath = preg_replace("/(.+?)\/*$/", "\\1/",  $filepath);

		$list = $this->list_files($filepath);

		if ($list !== FALSE AND count($list) > 0)
		{
			foreach ($list as $item)
			{
				// If we can't delete the item it's probaly a folder so
				// we'll recursively call delete_dir()
				if ( ! @ftp_delete($this->_conn_id, $item))
				{
					$this->delete_dir($item);
				}
			}
		}

		$result = @ftp_rmdir($this->_conn_id, $filepath);

		if ($result === FALSE)
		{
			if ($this->_debug == TRUE)
			{
				throw new Fuel_Exception('ftp_unable_to_delete');
			}
			return FALSE;
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Set file permissions
	 *
	 * @access	public
	 * @param	string 	the file path
	 * @param	string	the permissions
	 * @return	bool
	 */
	function chmod($path, $perm)
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		// Permissions can only be set when running PHP 5
		if ( ! function_exists('ftp_chmod'))
		{
			if ($this->_debug == TRUE)
			{
				throw new Fuel_Exception('ftp_unable_to_chmod');
			}
			return FALSE;
		}

		$result = @ftp_chmod($this->_conn_id, $perm, $path);

		if ($result === FALSE)
		{
			if ($this->_debug == TRUE)
			{
				throw new Fuel_Exception('ftp_unable_to_chmod');
			}
			return FALSE;
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * FTP List files in the specified directory
	 *
	 * @access	public
	 * @return	array
	 */
	function list_files($path = '.')
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		return ftp_nlist($this->_conn_id, $path);
	}

	// ------------------------------------------------------------------------

	/**
	 * Read a directory and recreate it remotely
	 *
	 * This function recursively reads a folder and everything it contains (including
	 * sub-folders) and creates a mirror via FTP based on it.  Whatever the directory structure
	 * of the original file path will be recreated on the server.
	 *
	 * @access	public
	 * @param	string	path to source with trailing slash
	 * @param	string	path to destination - include the base folder with trailing slash
	 * @return	bool
	 */
	function mirror($locpath, $rempath)
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		// Open the local file path
		if ($fp = @opendir($locpath))
		{
			// Attempt to open the remote file path.
			if ( ! $this->change_dir($rempath, TRUE))
			{
				// If it doesn't exist we'll attempt to create the direcotory
				if ( ! $this->mkdir($rempath) OR ! $this->change_dir($rempath))
				{
					return FALSE;
				}
			}

			// Recursively read the local directory
			while (FALSE !== ($file = readdir($fp)))
			{
				if (@is_dir($locpath.$file) && substr($file, 0, 1) != '.')
				{
					$this->mirror($locpath.$file."/", $rempath.$file."/");
				}
				elseif (substr($file, 0, 1) != ".")
				{
					// Get the file extension so we can se the upload type
					$ext = $this->_getext($file);
					$mode = $this->_settype($ext);

					$this->upload($locpath.$file, $rempath.$file, $mode);
				}
			}
			return TRUE;
		}

		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the upload type
	 *
	 * @access	private
	 * @param	string
	 * @return	string
	 */
	function _settype($ext)
	{
		$text_types = array(
			'txt',
			'text',
			'php',
			'phps',
			'php4',
			'js',
			'css',
			'htm',
			'html',
			'phtml',
			'shtml',
			'log',
			'xml'
		);


		return in_array($ext, $text_types) ? 'ascii' : 'binary';
	}

	// ------------------------------------------------------------------------

	/**
	 * Close the connection
	 *
	 * @access	public
	 * @param	string	path to source
	 * @param	string	path to destination
	 * @return	bool
	 */
	function close()
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		@ftp_close($this->_conn_id);
	}

	function  __destruct()
	{
		$this->close();
	}

}

/* End of file ftp.php */