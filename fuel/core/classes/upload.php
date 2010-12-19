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

class Upload {

	/* ---------------------------------------------------------------------------
	 * STATIC PROPERTIES
	 * --------------------------------------------------------------------------- */

	/**
	 * @var array default configuration values
	 */
	protected static $_defaults = array(
		'auto_process'		=> false,
		// validation settings
		'max_size'			=> 0,
		'max_length'		=> 0,
		'ext_whitelist'		=> array(),
		'ext_blacklist'		=> array(),
		'type_whitelist'	=> array(),
		'type_blacklist'	=> array(),
		'mime_whitelist'	=> array(),
		'mime_blacklist'	=> array(),
		// save settings
		'path'				=> '',
		'prefix'			=> '',
		'suffix'			=> '',
		'extension'			=> '',
		'create_path'		=> true,
		'path_chmod'		=> 0777,
		'file_chmod'		=> 0666,
		'auto_rename'		=> true,
		'overwrite'			=> false,
		'randomize'			=> false,
		'normalize'			=> false
	);

	/**
	 * @var array configuration of this instance
	 */
	protected static $config = array();

	/**
	 * @var array normalized $_FILES array
	 */
	protected static $files = array();

	/**
	 * @var bool indicator of valid uploads
	 */
	protected static $valid = false;

	/* ---------------------------------------------------------------------------
	 * STATIC METHODS
	 * --------------------------------------------------------------------------- */

	public static function _init()
	{
		// get the config for this upload
		App\Config::load('upload', true);

		static::$config = array_merge(static::$_defaults, App\Config::get('upload', array()));

		// define our additional error constants
		define('UPLOAD_ERR_MAX_SIZE',				101);
		define('UPLOAD_ERR_EXT_BLACKLISTED',		102);
		define('UPLOAD_ERR_EXT_NOT_WHITELISTED',	103);
		define('UPLOAD_ERR_TYPE_BLACKLISTED',		104);
		define('UPLOAD_ERR_TYPE_NOT_WHITELISTED',	105);
		define('UPLOAD_ERR_MIME_BLACKLISTED', 		106);
		define('UPLOAD_ERR_MIME_NOT_WHITELISTED',	107);
		define('UPLOAD_ERR_MAX_FILENAME_LENGTH',	108);
		define('UPLOAD_ERR_MOVE_FAILED',			109);
		define('UPLOAD_ERR_DUPLICATE_FILE',			110);

		if (static::$config['auto_process'])
		{
			self::process();
		}
	}

	// ---------------------------------------------------------------------------

	public static function is_valid()
	{
		return static::$valid;
	}

	// ---------------------------------------------------------------------------

	public static function get_files()
	{
		$files = array();

		foreach(static::$files as $file)
		{
			if ($file['error'] == 0)
			{
				$files[] = $file;
			}
		}

		return $files;
	}

	// ---------------------------------------------------------------------------

	public static function get_errors()
	{
		$files = array();

		foreach(static::$files as $file)
		{
			if ($file['error'] != 0)
			{
				$files[] = $file;
			}
		}

		return $files;
	}

	// ---------------------------------------------------------------------------

	/**
	 * Normalize the $_FILES array and store the result in $files
	 *
	 * @return	void
	 */
	public static function process($config = array())
	{
		// process runtime config
		if (is_array($config))
		{
			static::$config = array_merge(static::$config, $config);
		}

		// reset the processed files array
		static::$files = array();

		// process the uploaded files
		foreach($_FILES as $name => $value)
		{
			// store the form variable name
			$file = array('field' => $name);

			// if the variable is an array, store the index key
			if (is_array($value['name']))
			{
				$file['key'] = key($value['name']);
				$file['name'] = $value['name'][$file['key']];
				$file['type'] = $value['type'][$file['key']];
				$file['file'] = $value['tmp_name'][$file['key']];
				$file['error'] = $value['error'][$file['key']];
				$file['size'] = $value['size'][$file['key']];
			}
			else
			{
				$file = $value;
				$file['key'] = false;
				$file['file'] = $value['tmp_name'];
				unset($file['tmp_name']);
			}

			// skip this entry if no file was uploaded
			if ($file['error'] == UPLOAD_ERR_NO_FILE)
			{
				continue;
			}

			// get the file's mime type
			if ($file['error'] == 0)
			{
				$handle = finfo_open(FILEINFO_MIME_TYPE);
				$file['mimetype'] = finfo_file($handle, $file['file']);
				finfo_close($handle);
				if ($file['mimetype'] == 'application/octet-stream' and $file['type'] != $file['mimetype'])
				{
					$file['mimetype'] = $file['type'];
				}
			}
			// make sure it contains something valid
			if (empty($file['mimetype']))
			{
				$file['mimetype'] = 'application/octet-stream';
			}

			// strip the dot of dot-files
			$file['name'] = ltrim($file['name'], '.');

			// add some filename details
			$file['filename'] = pathinfo($file['name'], PATHINFO_FILENAME);
			$file['extension'] = pathinfo($file['name'], PATHINFO_EXTENSION);

			// does this upload exceed the maximum size?
			if ($file['error'] == 0 and ! empty(static::$config['max_size']) and $file['size'] > static::$config['max_size'])
			{
				$file['error'] = UPLOAD_ERR_MAX_SIZE;
			}

			// check the file extension black- and whitelists
			if ($file['error'] == 0)
			{
				if (in_array($file['extension'], (array) static::$config['ext_blacklist']))
				{
					$file['error'] = $file['error'] = UPLOAD_ERR_EXT_BLACKLISTED;
				}
				elseif ( ! empty(static::$config['ext_whitelist']) and ! in_array($file['extension'], (array) static::$config['ext_whitelist']))
				{
					$file['error'] = UPLOAD_ERR_EXT_NOT_WHITELISTED;
				}
			}

			// check the file type black- and whitelists
			if ($file['error'] == 0)
			{
				// split the mimetype info so we can run some tests
				preg_match('|^(.*)/(.*)|', $file['mimetype'], $mimeinfo);

				if (in_array($mimeinfo[1], (array) static::$config['type_blacklist']))
				{
					$file['error'] = $file['error'] = UPLOAD_ERR_TYPE_BLACKLISTED;
				}
				if ( ! empty(static::$config['type_whitelist']) and ! in_array($mimeinfo[1], (array) static::$config['type_whitelist']))
				{
					$file['error'] = UPLOAD_ERR_TYPE_NOT_WHITELISTED;
				}
			}

			// check the file mimetype black- and whitelists
			if ($file['error'] == 0)
			{
				if (in_array($file['mimetype'], (array) static::$config['mime_blacklist']))
				{
					$file['error'] = $file['error'] = UPLOAD_ERR_MIME_BLACKLISTED;
				}
				elseif ( ! empty(static::$config['ext_whitelist']) and ! in_array($file['mimetype'], (array) static::$config['ext_whitelist']))
				{
					$file['error'] = UPLOAD_ERR_MIME_NOT_WHITELISTED;
				}
			}

			// update the valid flag
			static::$valid = (static::$valid or ($file['error'] === 0));

			// store the normalized result
			static::$files[] = $file;
		}
	}

	// ---------------------------------------------------------------------------

	/**
	 * save uploaded file(s)
	 *
	 * @param	mixed	if int, $files element to move. if array, list of elements to move, if none, move all elements
	 * @param	string	path to move to
	 * @return	void
	 */
	public static function save()
	{
		// path to save the files to
		$path = static::$config['path'];

		// files to save
		$files = array();

		// check for parameters
		if (func_num_args())
		{
			foreach(func_get_args() as $param)
			{
				// string: new path to save to
				if (is_string($param))
				{
					$path = $param;
				}
				// array: list of $files indexes to save
				elseif(is_array($param))
				{
					$files = array();
					foreach($param as $key)
					{
						if (isset(static::$files[(int) $key]))
						{
							$files[(int) $key] = static::$files[(int) $key];
						}
					}
				}
				// integer: files index to save
				elseif(is_numeric($param))
				{
					if (isset(static::$files[$param]))
					{
						$files = array(static::$files[$param]);
					}
				}
			}
		}
		else
		{
			// save all files
			$files = static::$files;
		}

		// anything to save?
		if (empty($files))
		{
			throw new Exception('No uploaded files are selected.');
		}

		// make sure we have a valid path
		$path = rtrim($path, DS).DS;
		if (empty($path))
		{
			throw new Exception('Can\'t move the uploaded file. No destination path is specified.');
		}
		if ( ! is_dir($path) and (bool) static::$config['create_path'])
		{
			$oldumask = umask(0);
			@mkdir($path, static::$config['path_chmod'], true);
			umask($oldumask);
		}
		if ( ! is_dir($path))
		{
			throw new Exception('Can\'t move the uploaded file. Destination path specified does not exist.');
		}

		// now that we have a path, let's save the files
		foreach($files as $key => $file)
		{
			// skip all files in error
			if ($file['error'] != 0)
			{
				continue;
			}

			// do we need to generate a random filename?
			if ( (bool) static::$config['randomize'])
			{
				$filename = md5(serialize($file));
			}
			else
			{
				$filename  = $file['filename'];
				if ( (bool) static::$config['normalize'])
				{
					$filename = App\Inflector::friendly_title($filename, '_');
				}
			}

			// add prefix/suffix/extension
			$save_as = array(static::$config['prefix'], $filename, static::$config['suffix'], '.');
			$save_as[] = empty(static::$config['extension']) ? $file['extension'] : static::$config['extension'];

			// does the filename exceed the maximum length?
			if ( ! empty(static::$config['max_length']) and strlen(implode('', $save_as)) > static::$config['max_length'])
			{
				static::$files[$key]['error'] = UPLOAD_ERR_MAX_FILENAME_LENGTH;
				continue;
			}


			// check if the file already exists
			if (file_exists($path.implode('', $save_as)))
			{
				if ( (bool) static::$config['auto_rename'])
				{
					$counter = 0;
					do
					{
						$save_as[3] = '_'.++$counter.'.';
					}
					while (file_exists($path.implode('', $save_as)));
				}
				else
				{
					if ( ! (bool) static::$config['overwrite'])
					{
						static::$files[$key]['error'] = UPLOAD_ERR_DUPLICATE_FILE;
						continue;
					}
				}
			}

			// if no error was detected, move the file
			if (static::$files[$key]['error'] == 0)
			{
				// save the additional information
				static::$files[$key]['saved_to'] = $path;
				static::$files[$key]['saved_as'] = implode('', $save_as);

				if( ! @move_uploaded_file($file['file'], $path.implode('', $save_as)) )
				{
					static::$files[$key]['error'] = UPLOAD_ERR_MOVE_FAILED;
				}
				else
				{
					$oldumask = umask(0);
					@chmod($path.$save_as, static::$config['file_chmod']);
					umask($oldumask);
				}
			}
		}
	}

}

/* End of file upload.php */
