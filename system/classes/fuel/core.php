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

/**
 * The core of the framework.
 *
 * @package		Fuel
 * @subpackage	Core
 * @category	Core
 */
class Fuel_Core {

	public static $initialized = false;

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
		Fuel::$initialized and die('Can only initialize Fuel once.');

		Fuel::$_paths = array(APPPATH, SYSPATH);

		spl_autoload_register(array('Fuel', 'autoload'));

		Config::load('config');
		Config::load('routes', 'routes');

		if (Config::get('base_url') === false)
		{
			if (isset($_SERVER['SCRIPT_NAME']))
			{
				Config::set('base_url', dirname($_SERVER['SCRIPT_NAME']).'/');
			}
		}

		// Set some server options
		setlocale(LC_ALL, Config::get('locale'));

		Fuel::$initialized = true;
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
		// This is used later
		$called_class = $class;

		$class = (MBSTRING) ? mb_strtolower($class, INTERNAL_ENC) : strtolower($class);
		$parts = explode('_', $class);
		$folder = array_pop($parts);

		// If the class is not a Controller, or is a Core Class, then look in 'classes'
		if (($folder != 'controller' and $folder != 'model') or empty($parts) or $parts[0] == 'fuel')
		{
			$file = str_replace('_', DIRECTORY_SEPARATOR, $class);
			$folder = 'classes';
		}
		
		// If it is a controller or model, then look in 'controllers' or 'models'
		else
		{
			$folder .= 's';
			$file = implode(DIRECTORY_SEPARATOR, $parts);
		}

		if ($path = Fuel::find_file($folder, $file))
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
			
			// if it has a static init() method, then call it now.
			if (is_callable($called_class.'::init'))
			{
				call_user_func($called_class.'::init');
			}
			
			return true;
		}
		elseif ($folder == 'classes')
		{
			$path = SYSPATH.$folder.DIRECTORY_SEPARATOR.'fuel'.DIRECTORY_SEPARATOR.strtolower($file).'.php';
			if (is_file($path))
			{
				require($path);
				eval('class '.$called_class.' extends Fuel_'.$called_class.' {}');
				return true;
			}
		}

		// Class is not in the filesystem
		return false;
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
	public static function find_file($directory, $file, $ext = '.php')
	{
		$path = $directory.DIRECTORY_SEPARATOR.strtolower($file).$ext;

		$found = false;
		foreach (Fuel::$_paths as $dir)
		{
			if (is_file($dir.$path))
			{
				$found = $dir.$path;
				break;
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