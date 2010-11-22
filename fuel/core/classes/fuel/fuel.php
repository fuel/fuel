<?php
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

namespace Fuel;

/**
 * The core of the framework.
 *
 * @package		Fuel
 * @subpackage	Core
 * @category	Core
 */
class Fuel {

	public static $initialized = false;

	public static $env;

	public static $bm = true;

	public static $locale;

	protected static $_paths = array();

	protected static $packages = array();

	final private function __construct() { }

	/**
	 * Initializes the framework.  This can only be called once.
	 *
	 * @access	public
	 * @return	void
	 */
	public static function init($autoloaders)
	{
		if (static::$initialized)
		{
			throw new Exception("You can't initialize Fuel more than once.");
		}

		static::$_paths = array(APPPATH, COREPATH);

		// Add the core and optional application loader to the packages array
		static::$packages = $autoloaders;

		register_shutdown_function('Error::shutdown_handler');
		set_exception_handler('Error::exception_handler');
		set_error_handler('Error::error_handler');

		// Start up output buffering
		ob_start();

		Config::load('config');

		static::$bm = Config::get('benchmarking', true);
		static::$env = Config::get('environment');
		static::$locale = Config::get('locale');

		Config::load('routes', 'routes');
		Route::$routes = Config::get('routes');

		//Load in the packages
		foreach (Config::get('packages', array()) as $package)
		{
			static::add_package($package);
		}

		if (Config::get('base_url') === false)
		{
			if (isset($_SERVER['SCRIPT_NAME']))
			{
				$base_url = dirname($_SERVER['SCRIPT_NAME']);

				// Add a slash if it is missing
				substr($base_url, -1, 1) == '/' OR $base_url .= '/';

				Config::set('base_url', $base_url);
			}
		}

		// Set some server options
		setlocale(LC_ALL, static::$locale);

		static::$initialized = true;
	}
	
	/**
	 * Cleans up Fuel execution, ends the output buffering, and outputs the
	 * buffer contents.
	 * 
	 * @access	public
	 * @return	void
	 */
	public static function finish()
	{
		// Grab the output buffer
		$output = ob_get_clean();

		// Send the buffer to the browser.
		echo $output;
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
		$path = $directory.DS.strtolower($file).$ext;

		$found = false;
		foreach (static::$_paths as $dir)
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

	/**
	 * Adds a package or multiple packages to the stack.
	 * 
	 * Examples:
	 * 
	 * static::add_package('foo');
	 * static::add_package(array('foo' => PKGPATH.'bar/foo/'));
	 * 
	 * @access	public
	 * @param	array|string	the package name or array of packages
	 * @return	void
	 */
	public static function add_package($package)
	{
		if ( ! is_array($package))
		{
			$package = array($package => PKGPATH.$package.DS);
		}
		foreach ($package as $name => $path)
		{
			if (array_key_exists($name, static::$packages))
			{
				continue;
			}
			static::$packages[$name] = static::load($path.'autoload.php');
		}

		// Put the APP autoloader back on top
		spl_autoload_unregister(array(static::$packages['app'], 'load'));
		spl_autoload_register(array(static::$packages['app'], 'load'), true, true);
	}

	/**
	 * Removes a package from the stack.
	 * 
	 * @access	public
	 * @param	string	the package name
	 * @return	void
	 */
	public static function remove_package($package)
	{
		spl_autoload_unregister(array(static::$packages[$name], 'load'));
		unset(static::$packages[$name]);
	}

	/**
	 * Cleans a file path so that it does not contain absolute file paths.
	 * 
	 * @access	public
	 * @param	string	the filepath
	 * @return	string
	 */
	public static function clean_path($path)
	{
		static $search = array(APPPATH, COREPATH, DOCROOT);
		static $replace = array('APPPATH/', 'COREPATH/', 'DOCROOT/');
		return str_replace($search, $replace, $path);
	}
}

/* End of file core.php */
