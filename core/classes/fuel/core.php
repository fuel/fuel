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

	public static $loaded_classes = array();

	public static $classes = array(
		'Fuel_Arr'			=>	'classes/fuel/arr.php',
		'Fuel_Asset'		=>	'classes/fuel/asset.php',
		'Fuel_Benchmark'	=>	'classes/fuel/benchmark.php',
		'Fuel_Cache'		=>	'classes/fuel/cache.php',
		'Fuel_Config'		=>	'classes/fuel/config.php',
		'Fuel_Controller'	=>	'classes/fuel/controller.php',
		'Fuel_Cookie'		=>	'classes/fuel/cookie.php',
		'Fuel_DB'			=>	'classes/fuel/db.php',
		'Fuel_Debug'		=>	'classes/fuel/debug.php',
		'Fuel_Encrypt'		=>	'classes/fuel/encrypt.php',
		'Fuel_Env'			=>	'classes/fuel/env.php',
		'Fuel_Error'		=>	'classes/fuel/error.php',
		'Fuel_Exception'	=>	'classes/fuel/exception.php',
		'Fuel_Form'			=>	'classes/fuel/form.php',
		'Fuel_Ftp'			=>	'classes/fuel/ftp.php',
		'Fuel_Input'		=>	'classes/fuel/input.php',
		'Fuel_Lang'			=>	'classes/fuel/lang.php',
		'Fuel_Log'			=>	'classes/fuel/log.php',
		'Fuel_Migrate'		=>	'classes/fuel/migrate.php',
		'Fuel_Model'		=>	'classes/fuel/model.php',
		'Fuel_Output'		=>	'classes/fuel/output.php',
		'Fuel_Request'		=>	'classes/fuel/request.php',
		'Fuel_Route'		=>	'classes/fuel/route.php',
		'Fuel_Session'		=>	'classes/fuel/session.php',
		'Fuel_URI'			=>	'classes/fuel/uri.php',
		'Fuel_URL'			=>	'classes/fuel/url.php',
		'Fuel_View'			=>	'classes/fuel/view.php',
	);

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
		if (Fuel::$initialized)
		{
			throw new Fuel_Exception("You can't initialize Fuel more than once.");
		}

		Fuel::$_paths = array(APPPATH, COREPATH);

		if (is_file(APPPATH.'config/classes.php'))
		{
			Fuel::$classes = Fuel::$classes + Fuel::load(APPPATH.'config/classes.php');
		}

		spl_autoload_register(array('Fuel', 'autoload'));
		
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
	 * Autoloads the given class.
	 *
	 * @access	public
	 * @param	string	The name of the class
	 * @return	bool	Whether the class was loaded or not
	 */
	public static function autoload($class)
	{
		$found = false;
		$auto_alias = false;
		$called_class = $class;
	
		// First we check the class arrays
		if (isset(Fuel::$classes[$class]))
		{
			require ((strncmp($class, 'Fuel_', 5) === 0) ? COREPATH : APPPATH).Fuel::$classes[$class];
			$found = true;
		}
		elseif (isset(Fuel::$classes['Fuel_'.$class]))
		{
			require COREPATH.Fuel::$classes['Fuel_'.$class];
			$found = true;
			$auto_alias = true;
		}
		else
		{
			$class = (MBSTRING) ? mb_strtolower($class, INTERNAL_ENC) : strtolower($class);
			$file = str_replace('_', DS, $class);

			if ($path = Fuel::find_file('classes', $file))
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

				$found = true;
			}
		}

		if ($auto_alias)
		{
			$abstract = '';
			$class = new ReflectionClass('Fuel_'.$called_class);
			if ($class->isAbstract())
			{
				$abstract = 'abstract ';
			}
			eval($abstract.'class '.$called_class.' extends Fuel_'.$called_class.' { }');
		
			$found = true;
		}

		if ($found)
		{
			// if it has a static _init() method, then call it now.
			if (is_callable($called_class.'::_init'))
			{
				call_user_func($called_class.'::_init');
			}
			return true;
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
