<?php defined('COREPATH') or die('No direct script access.');
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

	public static $env;

	public static $bm = true;

	public static $locale;

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
		if (Fuel::$initialized)
		{
			throw new Fuel_Exception("You can't initialize Fuel more than once.");
		}

		Fuel::$_paths = array(APPPATH, COREPATH);

		register_shutdown_function('Error::shutdown_handler');
		set_exception_handler('Error::exception_handler');
		set_error_handler('Error::error_handler');

		// Start up output buffering
		ob_start();

		Config::load('config');

		Fuel::$bm = Config::get('benchmarking', true);
		Fuel::$env = Config::get('environment');
		Fuel::$locale = Config::get('locale');

		Config::load('routes', 'routes');
		Route::$routes = Config::get('routes');

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
		setlocale(LC_ALL, Fuel::$locale);

		Fuel::$initialized = true;
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
