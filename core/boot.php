<?php

// Get the start time and memory for use later
defined('FUEL_START_TIME') or define('FUEL_START_TIME', microtime(true));
defined('FUEL_START_MEM') or define('FUEL_START_MEM', memory_get_usage());

/**
 * Do we have access to mbstring?
 * Not every server has it installed by default
 * http://php.net/manual/en/mbstring.installation.php
 * We need this in order to work with UTF-8 strings
 */
define('MBSTRING', function_exists('mb_get_info'));

// if we have access to MBSTRING, set the internal encoding:
if (MBSTRING) mb_internal_encoding(INTERNAL_ENC);

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
define('APPPATH', realpath($app_path).DIRECTORY_SEPARATOR);
define('PKGPATH', realpath($package_path).DIRECTORY_SEPARATOR);
define('COREPATH', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);

// save a bit of memory by unsetting the path array
unset($app_path, $package_path);

// If the user has added a base.php to their app load it
if (is_file(APPPATH.'base.php'))
{
	require APPPATH.'base.php';
}

// Load in the core functions that are available app wide
require COREPATH.'base.php';

// Load in the core class
require COREPATH.'classes/fuel/core.php';

// If the Fuel class is overrided in the application folder
// load that, else load the core class.
if (is_file(APPPATH.'classes/fuel.php'))
{
	require APPPATH.'classes/fuel.php';
}
else
{
	require COREPATH.'classes/fuel.php';
}

// Initialize the framework
// and start buffering the output.
Fuel::init();

$request = Request::instance();
$request->execute();
echo $request->output;

Fuel::finish();

/* End of file boot.php */