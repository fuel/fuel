<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Carbon
 *
 * Carbon is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Carbon
 * @version		1.0
 * @author		Dan Horrigan <http://dhorrigan.com>
 * @license		Apache License v2.0
 * @copyright	2010 Dan Horrigan
 */

/**
 * The core of the framework.
 *
 * @package		Carbon
 * @subpackage	Core
 * @category	Core
 */
class Carbon_Core {

	public static $initialized = FALSE;

	public static $base_url = '';
	
	public static $index_file = FALSE;

	protected static $_paths = array();

	final private function __construct() { }

	/**
	 * Initializes the framework.  This can only be called once.
	 *
	 * @access	public
	 * @return	void
	 */
	public static function init()
	{
		// TODO: Replace die() and throw an exception.
		Carbon::$initialized AND die('Can only initialize Carbon once.');

		Carbon::$_paths = array(APPPATH, SYSPATH);

		spl_autoload_register(array('Carbon', 'autoload'));
		
		$config = Carbon::load(APPPATH.'config/config.php');

		if (isset($config['base_url']))
		{
			Carbon::$base_url = $config['base_url'];
		}
		else
		{
			if (isset($_SERVER['SCRIPT_NAME']))
			{
				Carbon::$base_url = dirname($_SERVER['SCRIPT_NAME']).'/';
			}
		}

		if (isset($config['index_file']))
		{
			Carbon::$index_file = $config['index_file'];
		}

		Carbon::$initialized = TRUE;
	}

	/**
	 * Autoloads the given class.
	 *
	 * @access	public
	 * @param	string	The name of the class
	 * @return	bool	Whether the class was loaded or not
	 */
	public static function autoload($class)
	{
		$file = str_replace('_', '/', strtolower($class));

		if ($path = Carbon::find_file('classes', $file))
		{
			if (is_array($path))
			{
				foreach ($path as $file)
				{
					require $file;
				}
			}
			else
			{
				require $path;
			}
			return TRUE;
		}

		// Class is not in the filesystem
		return FALSE;
	}

	/**
	 * Finds a file in the given directory.  It allows for a cascading filesystem.
	 *
	 * @access	public
	 * @param	string	The directory to look in.
	 * @param	string	The name of the file
	 * @param	string	The file extension
	 * @return	string	The path to the file
	 */
	public static function find_file($directory, $file, $ext = EXT)
	{
		$path = $directory.DIRECTORY_SEPARATOR.$file.$ext;

		if(in_array($directory, array('i18n')))
		{
			$paths = array_reverse(Carbon::$_paths);
			$found = array();

			foreach ($paths as $dir)
			{
				if (is_file($dir.$path))
				{
					// This path has a file, add it to the list
					$found[] = $dir.$path;
				}
			}

		}
		else
		{
			$found = FALSE;
			foreach (Carbon::$_paths as $dir)
			{
				if (is_file($dir.$path))
				{
					$found = $dir.$path;

					break;
				}
			}
		}
		return $found;
	}

	/**
	 * Loading in the given file
	 *
	 * @access	public
	 * @param	string	The path to the file
	 * @return	mixed	The results of the include
	 */
	public static function load($file)
	{
		return include $file;
	}

}

/* End of file core.php */