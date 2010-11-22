<?php

// Get the start time and memory for use later
defined('FUEL_START_TIME') or define('FUEL_START_TIME', microtime(true));
defined('FUEL_START_MEM') or define('FUEL_START_MEM', memory_get_usage());

// This is purely for ease of use, creating an alias of DS
define('DS', DIRECTORY_SEPARATOR);

/**
 * Do we have access to mbstring?
 * We need this in order to work with UTF-8 strings
 */
define('MBSTRING', function_exists('mb_get_info'));

// Determine the app path
if ( ! is_dir($app_path) and is_dir(DOCROOT.$app_path))
{
	$app_path = DOCROOT.$app_path;
}

// Determine the package path
if ( ! is_dir($package_path) and is_dir(DOCROOT.$package_path))
{
	$package_path = DOCROOT.$package_path;
}

// Define the global path constants
define('APPPATH', realpath($app_path).DS);
define('PKGPATH', realpath($package_path).DS);
define('COREPATH', realpath(dirname(__FILE__)).DS);

// save a bit of memory by unsetting the path array
unset($app_path, $package_path);

define('APP_NAMESPACE', trim($app_namespace, '\\'));

// If the user has added a base.php to their app load it
if (is_file(APPPATH.'base.php'))
{
	require APPPATH.'base.php';
}

// Load in the core functions that are available app wide
require COREPATH.'base.php';


/**
 * Load in the autoloader class then register any app and core autoloaders.
 */
require COREPATH.'classes'.DS.'autoloader.php';

$autoloaders['core'] = require COREPATH.'autoload.php';
if (is_file(APPPATH.'autoload.php'))
{
	$autoloaders['app'] = require APPPATH.'autoload.php';
}


// Load in the core class
require COREPATH.'classes'.DS.'fuel'.DS.'fuel.php';

// If the Fuel class is overrided in the application folder
// load that, else load the core class.
if (is_file(APPPATH.'classes'.DS.'fuel.php'))
{
	require APPPATH.'classes'.DS.'fuel.php';
}

// Initialize the framework
// and start buffering the output.
Fuel\Fuel::init($autoloaders);

$request = Fuel\Request::instance();
$request->execute();
echo $request->output;

// Call all the shutdown events
Fuel\Event::shutdown();

Fuel\Fuel::finish();

/* End of file boot.php */