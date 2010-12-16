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

use Fuel\Application\Config;
use Fuel\Application\Profiler;
use Fuel\Application\Input;
use Fuel\Application\Security;
use Fuel\Application\Benchmark;
use Fuel\Application\Autoloader;
use Fuel\Application\Exception;

/**
 * Holds Environment constants.
 */
class Env {
	const TEST = 'test';
	const DEVELOPMENT = 'dev';
	const QA = 'qa';
	const PRODUCTION = 'production';
}

/**
 * The core of the framework.
 *
 * @package		Fuel
 * @subpackage	Core
 * @category	Core
 */
class Fuel {

	const VERSION = '1.0.0-dev';

	public static $initialized = false;

	public static $env;

	public static $bm = true;

	public static $profile = false;

	public static $locale;

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
			throw new Exception("You can't initialize Fuel more than once.");
		}

		static::$_paths = array(APPPATH, COREPATH);

		register_shutdown_function('fuel_shutdown_handler');
		set_exception_handler('fuel_exception_handler');
		set_error_handler('fuel_error_handler');

		// Start up output buffering
		ob_start();

		Config::load('config');
		static::$profile = Config::get('profile', false);

		if (static::$profile)
		{
			Profiler::init();
			Profiler::mark(__METHOD__.' Start');
		}

		static::$is_cli = (bool) (php_sapi_name() == 'cli');

		/**
		 * WARNING:  The order of the following statements is very important.
		 * Re-arranging these will cause unexpected results.
		 */
		if ( ! static::$is_cli)
		{
			if (Config::get('base_url') === null)
			{
				$base_url = '';
				if(Input::server('http_host'))
				{
					$base_url .= Input::protocol().'://'.Input::server('http_host');
				}
				if (Input::server('script_name'))
				{
					$base_url .= str_replace('\\', '/', dirname(Input::server('script_name')));

					// Add a slash if it is missing
					$base_url = rtrim($base_url, '/').'/';
					Config::set('base_url', $base_url);
				}
			}

			URI::detect();
		}
		// Run Input Filtering
		Security::clean_input();

		static::$bm = Config::get('benchmarking', true);
		static::$env = Config::get('environment');
		static::$locale = Config::get('locale');

		//Load in the packages
		foreach (Config::get('packages', array()) as $package)
		{
			static::add_package($package);
		}

		// Set some server options
		setlocale(LC_ALL, static::$locale);

		// Always load classes, config & language set in always_load.php config
		static::always_load();

		static::$initialized = true;

		if (static::$profile)
		{
			Profiler::mark(__METHOD__.' End');
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
		// Grab the output buffer
		$output = ob_get_clean();
		if (static::$profile)
		{
			if (preg_match("|</body>.*?</html>|is", $output))
			{
				$output  = preg_replace("|</body>.*?</html>|is", '', $output);
				$output .= Profiler::output();
				$output .= '</body></html>';
			}
			else
			{
				$output .= Profiler::output();
			}
		}

		$bm = Benchmark::app_total();

		// TODO: There is probably a better way of doing this, but this works for now.
		$output = \str_replace(
				array('{exec_time}', '{mem_usage}', '{query_count}'),
				array(round($bm[0], 4), round($bm[1] / pow(1024, 2), 3), DB::$query_count),
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
		return $found;
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
			\array_unshift(static::$_paths, $path);
		}
		else
		{
			// find APPPATH index
			$insert_at = \array_search(APPPATH, static::$_paths) + 1;
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
			Route::load_routes(true);
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
		// First attempt registered prefixes
		$mod_path = Autoloader::prefix_path(ucfirst($name).'_');
		// Or try registered module paths
		if ($mod_path === false)
		{
			foreach (Config::get('module_paths', array()) as $path)
			{
				if (is_dir($mod_check_path = $path.strtolower($name).DS))
				{
					// Load module and end search
					$mod_path = $mod_check_path;
					$ns = 'Fuel\\Application\\'.ucfirst($name);
					Autoloader::add_namespaces(array(
						$ns					=> $mod_path.'classes'.DS,
						$ns.'\\Model'		=> $mod_path.'classes'.DS.'model'.DS,
						$ns.'\\Controller'	=> $mod_path.'classes'.DS.'controller'.DS,
					), true);
					Autoloader::add_namespace_aliases(array(
						$ns.'\\Controller'	=> 'Fuel\\Application',
						$ns.'\\Model'		=> 'Fuel\\Application',
						$ns					=> 'Fuel\\Application',
					), true);
					break;
				}
			}
		}

		// not found
		if ($mod_path === false)
		{
			return false;
		}

		// Active modules get their path prefixed and routes loaded
		if ($active)
		{
			static::add_path($mod_path, true);

			// We want modules to be able to have their own routes, so we reload routes.
			Route::load_routes(true);
			return $mod_path;
		}

		static::add_path($mod_path);
		return $mod_path;
	}

	/**
	 * Always load classes, config & language files set in always_load.php config
	 */
	public static function always_load($array = null)
	{
		$array = is_null($array) ? Config::get('always_load', array()) : $array;

		foreach ($array['classes'] as $class)
		{
			if ( ! class_exists($class))
			{
				throw new Exception('Always load class does not exist.');
			}
		}

		/**
		 * Config and Lang must be either just the filename, example: array(filename)
		 * or the filename as key and the group as value, example: array(filename => some_group)
		 */

		foreach ($array['config'] as $config => $config_group)
		{
			Config::load((is_int($config) ? $config_group : $config), (is_int($config) ? true : $config_group));
		}

		foreach ($array['language'] as $lang => $lang_group)
		{
			Lang::load((is_int($lang) ? $lang_group : $lang), (is_int($lang) ? true : $lang_group));
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
