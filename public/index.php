<?php
/**
 * Set all the paths
 */
$app_path		= '../fuel/app';
$package_path	= '../fuel/packages';
$core_path		= '../fuel/core';

/**
 * If you want to use the server timezone, leave this null.  If the server does not have a default timezone set an
 * exception will be thrown.
 */
$default_timezone = null;

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
if ($default_timezone != null)
{
	date_default_timezone_set($default_timezone);
}
elseif ( ! ini_get('date.timezone'))
{
	die('Your server does not have a default timezone set.  Please open up index.php and set a default timezone on line 25.');
}

// Get the start time and memory for use later
defined('FUEL_START_TIME') or define('FUEL_START_TIME', microtime(true));
defined('FUEL_START_MEM') or define('FUEL_START_MEM', memory_get_usage());

// This is purely for ease of use, creating an alias of DS
define('DS', DIRECTORY_SEPARATOR);

define('CRLF', sprintf('%s%s', chr(13), chr(10)));

// Determine the app path
if ( ! is_dir($app_path) and is_dir(DOCROOT.$app_path))
{
	$app_path = DOCROOT.$app_path;
}

// Determine the core path
if ( ! is_dir($core_path) and is_dir(DOCROOT.$core_path))
{
	$core_path = DOCROOT.$core_path;
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


// If the user has added a base.php to their app load it
if (is_file(APPPATH.'base.php'))
{
	require APPPATH.'base.php';
}
// Load in the core functions that are available app wide
require COREPATH.'base.php';

// Load in the autoloader class then register any app and core autoloaders.
require COREPATH.'classes'.DS.'autoloader.php';
require COREPATH.'autoload.php';
require APPPATH.'autoload.php';

require COREPATH.'classes'.DS.'fuel.php';

if (is_file(APPPATH.'classes'.DS.'fuel.php'))
{
	require APPPATH.'classes'.DS.'fuel.php';
}
else
{
	class_alias('Fuel\\Core\\Fuel', 'Fuel\\App\\Fuel');
}

Fuel\App\Autoloader::register();

// Initialize the framework
Fuel\App\Fuel::init();

Fuel\App\Request::factory()
                ->execute()
                ->send_headers()
                ->output();

// Call all the shutdown events
Fuel\App\Event::shutdown();
Fuel\App\Fuel::finish();


/* End of file index.php */