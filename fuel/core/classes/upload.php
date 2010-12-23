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
	 * ERROR CODE CONSTANTS
	 * --------------------------------------------------------------------------- */

	// duplicate the PHP standard error codes for consistency
	const UPLOAD_ERR_OK						= UPLOAD_ERR_OK;
	const UPLOAD_ERR_INI_SIZE				= UPLOAD_ERR_INI_SIZE;
	const UPLOAD_ERR_FORM_SIZE				= UPLOAD_ERR_FORM_SIZE;
	const UPLOAD_ERR_PARTIAL				= UPLOAD_ERR_PARTIAL;
	const UPLOAD_ERR_NO_FILE				= UPLOAD_ERR_NO_FILE;
	const UPLOAD_ERR_NO_TMP_DIR				= UPLOAD_ERR_NO_TMP_DIR;
	const UPLOAD_ERR_CANT_WRITE				= UPLOAD_ERR_CANT_WRITE;
	const UPLOAD_ERR_EXTENSION				= UPLOAD_ERR_EXTENSION;

	// and add our own error codes
	const UPLOAD_ERR_MAX_SIZE				= 101;
	const UPLOAD_ERR_EXT_BLACKLISTED		= 102;
	const UPLOAD_ERR_EXT_NOT_WHITELISTED	= 103;
	const UPLOAD_ERR_TYPE_BLACKLISTED		= 104;
	const UPLOAD_ERR_TYPE_NOT_WHITELISTED	= 105;
	const UPLOAD_ERR_MIME_BLACKLISTED		= 106;
	const UPLOAD_ERR_MIME_NOT_WHITELISTED	= 107;
	const UPLOAD_ERR_MAX_FILENAME_LENGTH	= 108;
	const UPLOAD_ERR_MOVE_FAILED			= 109;
	const UPLOAD_ERR_DUPLICATE_FILE 		= 110;

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
		'normalize'			=> false,
		'change_case'		=> false
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

	/**
	 * class initialisation, load the config and process $_FILES if needed
	 *
	 * @return	void
	 */
	public static function _init()
	{
		// get the config for this upload
		App\Config::load('upload', true);

		// make sure we have defaults for those not defined
		static::$config = array_merge(static::$_defaults, App\Config::get('upload', array()));

		static::$config['auto_process'] and self::process();
	}

	// ---------------------------------------------------------------------------

	/**
	 * Check if we have valid files
	 *
	 * @return	bool	true if static:$files contains uploaded files that are valid
	 */
	public static function is_valid()
	{
		return static::$valid;
	}

	// ---------------------------------------------------------------------------

	/**
	 * Get the list of validated files
	 *
	 * @return	array	list of uploaded files that are validated
	 */
	public static function get_files($index = null)
	{
		if (is_null($index) or ! isset(static::$files[$index]))
		{
			return array_filter(static::$files, function($file) { return $file['error'] == 0; } );
		}
		else
		{
			return static::$files[$index];
		}
	}

	// ---------------------------------------------------------------------------

	/**
	 * Get the list of non-validated files
	 *
	 * @return	array	list of uploaded files that failed to validate
	 */
	public static function get_errors($index = null)
	{
		if (is_null($index) or ! isset(static::$files[$index]) or $files[$index]['error'] == 0)
		{
			return array_filter(static::$files, function($file) { return $file['error'] != 0; } );
		}
		else
		{
			return static::$files[$index];
		}
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
			if ($file['error'] == static::UPLOAD_ERR_NO_FILE)
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

			// add some filename details (pathinfo can't be trusted with utf-8 filenames!)
			$file['extension'] = ltrim(strrchr(ltrim($file['name'], '.'), '.'),'.');
			if (empty($file['extension']))
			{
				$file['filename'] = $file['name'];
			}
			else
			{
				$file['filename'] = substr($file['name'], 0, strlen($file['name'])-(strlen($file['extension'])+1));
			}

			// does this upload exceed the maximum size?
			if ($file['error'] == 0 and ! empty(static::$config['max_size']) and $file['size'] > static::$config['max_size'])
			{
				$file['error'] = static::UPLOAD_ERR_MAX_SIZE;
			}

			// check the file extension black- and whitelists
			if ($file['error'] == 0)
			{
				if (in_array($file['extension'], (array) static::$config['ext_blacklist']))
				{
					$file['error'] = $file['error'] = static::UPLOAD_ERR_EXT_BLACKLISTED;
				}
				elseif ( ! empty(static::$config['ext_whitelist']) and ! in_array($file['extension'], (array) static::$config['ext_whitelist']))
				{
					$file['error'] = static::UPLOAD_ERR_EXT_NOT_WHITELISTED;
				}
			}

			// check the file type black- and whitelists
			if ($file['error'] == 0)
			{
				// split the mimetype info so we can run some tests
				preg_match('|^(.*)/(.*)|', $file['mimetype'], $mimeinfo);

				if (in_array($mimeinfo[1], (array) static::$config['type_blacklist']))
				{
					$file['error'] = $file['error'] = static::UPLOAD_ERR_TYPE_BLACKLISTED;
				}
				if ( ! empty(static::$config['type_whitelist']) and ! in_array($mimeinfo[1], (array) static::$config['type_whitelist']))
				{
					$file['error'] = static::UPLOAD_ERR_TYPE_NOT_WHITELISTED;
				}
			}

			// check the file mimetype black- and whitelists
			if ($file['error'] == 0)
			{
				if (in_array($file['mimetype'], (array) static::$config['mime_blacklist']))
				{
					$file['error'] = $file['error'] = static::UPLOAD_ERR_MIME_BLACKLISTED;
				}
				elseif ( ! empty(static::$config['ext_whitelist']) and ! in_array($file['mimetype'], (array) static::$config['ext_whitelist']))
				{
					$file['error'] = static::UPLOAD_ERR_MIME_NOT_WHITELISTED;
				}
			}

			// update the valid flag
			static::$valid = (static::$valid or ($file['error'] === 0));

			// store the normalized and validated result
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
				// string => new path to save to
				if (is_string($param))
				{
					$path = $param;
				}
				// array => list of $files indexes to save
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
				// integer => files index to save
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
		$oldumask = umask(0);
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

			// array with the final filename
			$save_as = array(
				static::$config['prefix'],
				$filename,
				static::$config['suffix'],
				'',
				'.',
				empty(static::$config['extension']) ? $file['extension'] : static::$config['extension']
			);
			// remove the dot if no extension is present
			if (empty($save_as[5]))
			{
				$save_as[4] = '';
			}

			// need to modify case?
			switch(static::$config['change_case'])
			{
				case 'upper':
					$save_as = array_map(function($var) { return strtoupper($var); }, $save_as);
				break;

				case 'lower':
					$save_as = array_map(function($var) { return strtolower($var); }, $save_as);
				break;

				default:
				break;
			}


			// check if the file already exists
			if (file_exists($path.implode('', $save_as)))
			{
				if ( (bool) static::$config['auto_rename'])
				{
					$counter = 0;
					do
					{
						$save_as[3] = '_'.++$counter;
					}
					while (file_exists($path.implode('', $save_as)));
				}
				else
				{
					if ( ! (bool) static::$config['overwrite'])
					{
						static::$files[$key]['error'] = static::UPLOAD_ERR_DUPLICATE_FILE;
						continue;
					}
				}
			}

			// no need to store it as an array anymore
			$save_as = implode('', $save_as);

			// does the filename exceed the maximum length?
			if ( ! empty(static::$config['max_length']) and strlen($save_as) > static::$config['max_length'])
			{
				static::$files[$key]['error'] = static::UPLOAD_ERR_MAX_FILENAME_LENGTH;
				continue;
			}

			// if no error was detected, move the file
			if (static::$files[$key]['error'] == 0)
			{
				// save the additional information
				static::$files[$key]['saved_to'] = $path;
				static::$files[$key]['saved_as'] = $save_as;

				// move the uploaded file
				if( ! @move_uploaded_file($file['file'], $path.$save_as) )
				{
					static::$files[$key]['error'] = static::UPLOAD_ERR_MOVE_FAILED;
				}
				else
				{
					@chmod($path.$save_as, static::$config['file_chmod']);
				}
			}
		}
		umask($oldumask);
	}

}

/* End of file upload.php */
