<?php
/**
 * Thrust
 *
 * Thrust is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Thrust
 * @version		1.0
 * @author		Dan Horrigan <http://dhorrigan.com>
 * @license		Apache License v2.0
 * @copyright	2010 Dan Horrigan
 */

/**
 * Change these paths to point to the correct location.
 * The paths are relative to your index.php
 */
$thrust_paths = array(
	'application'	=> './thrust/application',	// The path to the application folder
	'modules'		=> './thrust/modules',		// The path to the modules folder
	'system'		=> './thrust/system'		// The path to the system folder
);

/**
 * Change this only if you have configured your server to
 * process PHP files with a different extension.
 */
define('EXT', '.php');

/**
 * In a production environment you will want to change this.
 */
error_reporting(E_ALL);

ini_set('display_errors', TRUE);


// The full path to this file
define('DOCROOT', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);

// Loop through the paths and check if they are absolute or relative.
foreach ($thrust_paths as &$folder)
{
	if ( ! is_dir($folder) AND is_dir(DOCROOT.$folder))
	{
		$folder = DOCROOT.$folder;
	}
}

// Define the global path constants
define('APPPATH', realpath($thrust_paths['application']).DIRECTORY_SEPARATOR);
define('MODPATH', realpath($thrust_paths['modules']).DIRECTORY_SEPARATOR);
define('SYSPATH', realpath($thrust_paths['system']).DIRECTORY_SEPARATOR);

// save a bit of memory by unsetting the path array
unset($thrust_paths);

// Load the base, low-level functions
require SYSPATH.'base'.EXT;

// Load in the core class
require SYSPATH.'classes/thrust/core'.EXT;

// If the Thrust class is overrided in the application folder
// load that, else load the core class.
if (is_file(APPPATH.'classes/thrust'.EXT))
{
	require APPPATH.'classes/thrust'.EXT;
}
else
{
	require SYSPATH.'classes/thrust'.EXT;
}

// Initialize the framework
Thrust::init();

$request = Request::instance();
$request->execute();
echo $request->output;

/* End of file index.php */