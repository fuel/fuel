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

namespace Fuel\Core;

use Fuel\App as App;

// Load in the Autoloader
require COREPATH.'classes'.DS.'autoloader.php';
require COREPATH.'autoload.php';
require APPPATH.'autoload.php';

/**
 * The core of the framework.
 *
 * @package		Fuel
 * @subpackage	Core
 * @category	Core
 */
class Fuel {

	/**
	 * Environment Constants.
	 */
	const TEST = 'test';
	const DEVELOPMENT = 'dev';
	const QA = 'qa';
	const PRODUCTION = 'production';

	const L_NONE = 0;
	const L_ERROR = 1;
	const L_DEBUG = 2;
	const L_INFO = 3;
	const L_ALL = 4;

	const VERSION = '1.0.0-dev';

	public static $initialized = false;

	public static $env = App\Fuel::DEVELOPMENT;

	public static $profiling = false;

	public static $locale;

	public static $path_cache = array();

	public static $caching = false;

	/**
	 * The amount of time to cache in seconds.
	 * @var	int	$cache_lifetime
	 */
	public static $cache_lifetime = 3600;

	protected static $cache_dir = '';

	public static $paths_changed = false;

	public static $is_cli = false;

	protected static $_paths = array();

	protected static $packages = array();

	final private function __construct() { }

	/**
	 * Initializes the framework.  This can only be called once.
	 *
	 * @access	public
	 * @return	void
	 */
	public static function init()
	{
		if (static::$initialized)
		{
			throw new App\Exception("You can't initialize Fuel more than once.");
		}
		App\Autoloader::register();

		static::$_paths = array(APPPATH, COREPATH);

		register_shutdown_function('fuel_shutdown_handler');
		set_exception_handler('fuel_exception_handler');
		set_error_handler('fuel_error_handler');

		// Start up output buffering
		ob_start();

		$config = static::load(APPPATH.'config/config.php');

		static::$profiling = isset($config['profiling']) ? $config['profiling'] : false;

		if (static::$profiling)
		{
			App\Profiler::init();
			App\Profiler::mark(__METHOD__.' Start');
		}

		static::$cache_dir = isset($config['cache_dir']) ? $config['cache_dir'] : APPPATH.'cache/';
		static::$caching = isset($config['caching']) ? $config['caching'] : false;
		static::$cache_lifetime = isset($config['cache_lifetime']) ? $config['cache_lifetime'] : 3600;

		if (static::$caching)
		{
			static::$path_cache = static::cache('Fuel::path_cache');
		}

		App\Config::load($config);

		static::$is_cli = (bool) (php_sapi_name() == 'cli');

		if ( ! static::$is_cli)
		{
			if (App\Config::get('base_url') === null)
			{
				App\Config::set('base_url', static::generate_base_url());
			}

			App\Uri::detect();
		}

		// Run Input Filtering
		App\Security::clean_input();

		static::$env = App\Config::get('environment');
		static::$locale = App\Config::get('locale');

		//Load in the packages
		foreach (App\Config::get('packages', array()) as $package)
		{
			static::add_package($package);
		}

		// Set some server options
		setlocale(LC_ALL, static::$locale);

		// Always load classes, config & language set in always_load.php config
		static::always_load();

		static::$initialized = true;

		if (static::$profiling)
		{
			App\Profiler::mark(__METHOD__.' End');
		}
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
		if (static::$caching && static::$paths_changed === true)
		{
			static::cache('Fuel::path_cache', static::$path_cache);
		}

		// Grab the output buffer
		$output = ob_get_clean();

		if (static::$profiling)
		{
			App\Profiler::mark('End of Fuel Execution');
			if (preg_match("|</body>.*?</html>|is", $output))
			{
				$output  = preg_replace("|</body>.*?</html>|is", '', $output);
				$output .= App\Profiler::output();
				$output .= '</body></html>';
			}
			else
			{
				$output .= App\Profiler::output();
			}
		}

		$bm = App\Profiler::app_total();

		// TODO: There is probably a better way of doing this, but this works for now.
		$output = \str_replace(
				array('{exec_time}', '{mem_usage}'),
				array(round($bm[0], 4), round($bm[1] / pow(1024, 2), 3)),
				$output
		);


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
	public static function find_file($directory, $file, $ext = '.php', $multiple = false)
	{
		$path = $directory.DS.strtolower($file).$ext;

		if (static::$path_cache !== null && array_key_exists($path, static::$path_cache))
		{
			return static::$path_cache[$path];
		}

		$found = $multiple ? array() : false;
		foreach (static::$_paths as $dir)
		{
			$file_path = $dir.$path;
			if (is_file($file_path))
			{
				if ($multiple)
				{
					$found[] = $file_path;
				}
				else
				{
					$found = $file_path;
					break;
				}
			}
		}

		static::$path_cache[$path] = $found;
		static::$paths_changed = true;

		return $found;
	}

	/**
	 * Generates a base url.
	 * 
	 * @return	string	the base url
	 */
	protected static function generate_base_url()
	{
		$base_url = '';
		if(App\Input::server('http_host'))
		{
			$base_url .= App\Input::protocol().'://'.App\Input::server('http_host');
		}
		if (App\Input::server('script_name'))
		{
			$base_url .= str_replace('\\', '/', dirname(App\Input::server('script_name')));

			// Add a slash if it is missing
			$base_url = rtrim($base_url, '/').'/';
		}
		return $base_url;
	}

	/**
	 * Add to paths which are used by Fuel::find_file()
	 *
	 * @param	string	the new path
	 * @param	bool	whether to add just behind the APPPATH or to prefix
	 */
	public static function add_path($path, $prefix = FALSE)
	{
		if ($prefix)
		{
			// prefix the path to the paths array
			array_unshift(static::$_paths, $path);
		}
		else
		{
			// find APPPATH index
			$insert_at = array_search(APPPATH, static::$_paths) + 1;
			// insert new path just behind the APPPATH
			array_splice(static::$_paths, $insert_at, 0, $path);
		}
	}

	public static function get_paths()
	{
		return static::$_paths;
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
			static::add_path($path);
			App\Route::load_routes(true);
			static::load($path.'autoload.php');
			static::$packages[$name] = true;
		}
	}

	/**
	 * Removes a package from the stack.
	 *
	 * @access	public
	 * @param	string	the package name
	 * @return	void
	 */
	public static function remove_package($name)
	{
		unset(static::$packages[$name]);
	}

	/**
	 * Add module
	 *
	 * Registers a given module as a class prefix and returns the path to the
	 * module. Won't register twice, will just return the path on a second call.
	 *
	 * @param	string	module name (lowercase prefix without underscore)
	 * @param	bool	whether it is an active package
	 */
	public static function add_module($name, $active = false)
	{
		$paths = App\Config::get('module_paths', array());
		
		if (count($paths) === 0)
		{
			return false;
		}

		$found = false;

		foreach ($paths as $path)
		{
			if (is_dir($mod_check_path = $path.strtolower($name).DS))
			{
				$found = true;

				// Load module and end search
				$mod_path = $mod_check_path;
				$ns = 'Fuel\\App\\'.ucfirst($name);
				App\Autoloader::add_namespaces(array(
					$ns					=> $mod_path.'classes'.DS,
					$ns.'\\Model'		=> $mod_path.'classes'.DS.'model'.DS,
					$ns.'\\Controller'	=> $mod_path.'classes'.DS.'controller'.DS,
				), true);
				App\Autoloader::add_namespace_aliases(array(
					$ns.'\\Controller'	=> 'Fuel\\App',
					$ns.'\\Model'		=> 'Fuel\\App',
					$ns					=> 'Fuel\\App',
				), true);
				break;
			}
		}

		// not found
		if ($found === false)
		{
			return false;
		}

		// Active modules get their path prefixed and routes loaded
		if ($active)
		{
			static::add_path($mod_path, true);

			// We want modules to be able to have their own routes, so we reload routes.
			App\Route::load_routes(true);
			return $mod_path;
		}

		static::add_path($mod_path);
		return $mod_path;
	}

	/**
	 * This method does basic filesystem caching.  It is used for things like path caching.
	 *
	 * This method is from KohanaPHP's Kohana class.
	 */
	public static function cache($name, $data = null, $lifetime = null)
	{
		// Cache file is a hash of the name
		$file = sha1($name).'.txt';

		// Cache directories are split by keys to prevent filesystem overload
		$dir = static::$cache_dir.DS.$file[0].$file[1].DS;

		if ($lifetime === NULL)
		{
			// Use the default lifetime
			$lifetime = static::$cache_lifetime;
		}

		if ($data === NULL)
		{
			if (is_file($dir.$file))
			{
				if ((time() - filemtime($dir.$file)) < $lifetime)
				{
					// Return the cache
					return json_decode(file_get_contents($dir.$file), true);
				}
				else
				{
					try
					{
						// Cache has expired
						unlink($dir.$file);
					}
					catch (Exception $e)
					{
						// Cache has mostly likely already been deleted,
						// let return happen normally.
					}
				}
			}

			// Cache not found
			return NULL;
		}

		if ( ! is_dir($dir))
		{
			// Create the cache directory
			mkdir($dir, 0777, TRUE);

			// Set permissions (must be manually set to fix umask issues)
			chmod($dir, 0777);
		}

		// Force the data to be a string
		$data = json_encode($data);

		try
		{
			// Write the cache
			return (bool) file_put_contents($dir.$file, $data, LOCK_EX);
		}
		catch (Exception $e)
		{
			// Failed to write cache
			return false;
		}
	}

	/**
	 * Always load classes, config & language files set in always_load.php config
	 */
	public static function always_load($array = null)
	{
		$array = is_null($array) ? App\Config::get('always_load', array()) : $array;

		foreach ($array['classes'] as $class)
		{
			if ( ! class_exists($class))
			{
				throw new App\Exception('Always load class does not exist.');
			}
		}

		/**
		 * Config and Lang must be either just the filename, example: array(filename)
		 * or the filename as key and the group as value, example: array(filename => some_group)
		 */

		foreach ($array['config'] as $config => $config_group)
		{
			App\Config::load((is_int($config) ? $config_group : $config), (is_int($config) ? true : $config_group));
		}

		foreach ($array['language'] as $lang => $lang_group)
		{
			App\Lang::load((is_int($lang) ? $lang_group : $lang), (is_int($lang) ? true : $lang_group));
		}
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
		static $search = array(APPPATH, COREPATH, DOCROOT, '\\');
		static $replace = array('APPPATH/', 'COREPATH/', 'DOCROOT/', '/');
		return str_ireplace($search, $replace, $path);
	}
}

/* End of file core.php */
