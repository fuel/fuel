<?php

use Fuel\Application as App;

/**
 * This is the path to the app directory.
 */
$app_path = '../fuel/app';

/**
 * This is the path to the package directory.
 */
$package_path = '../fuel/packages';

/**
 * This is the path to the core directory.
 */
$core_path = '../fuel/core';


/**
 * If you want to use a default namespace for your application you must specify
 * it here.
 */
$app_namespace = '';

/**
 * We disable short open tags by default so as to not confuse people.  They
 * also interfere with generating XML documents.
 */
ini_set('short_open_tag', 0);

/**
 * Define the internal encoding to use.
 *
 * @todo Re-evaluate how to handle this.
 */
define('INTERNAL_ENC', 'ISO-8859-1');

/**
 * Get the current path
 */
define('DOCROOT', realpath(__DIR__).DIRECTORY_SEPARATOR);




/**
 * Do not edit below this line unless you know what you are doing.
 */



// Get the start time and memory for use later
defined('FUEL_START_TIME') or define('FUEL_START_TIME', microtime(true));
defined('FUEL_START_MEM') or define('FUEL_START_MEM', memory_get_usage());

// This is purely for ease of use, creating an alias of DS
define('DS', DIRECTORY_SEPARATOR);

define('CRLF', sprintf('%s%s', chr(13), chr(10)));


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
define('COREPATH', realpath($core_path).DS);

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
$autoloaders['app'] = require APPPATH.'autoload.php';


// Load in the core class
require COREPATH.'classes'.DS.'fuel.php';

// If the Fuel class is overrided in the application folder
// load that, else load the core class.
if (is_file(APPPATH.'classes'.DS.'fuel.php'))
{
	require APPPATH.'classes'.DS.'fuel.php';
}

// Initialize the framework
// and start buffering the output.
App\Fuel::init($autoloaders);

$request = App\Request::factory();
$request->execute();
echo $request->output;

// Call all the shutdown events
App\Event::shutdown();

App\Fuel::finish();

/* End of file index.php */
