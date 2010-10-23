<?php
/**
 * Carbon
 *
 * Carbon is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Carbon
 * @version		1.0
 * @author		Dan Horrigan <http://dhorrigan.com>
 * @license		Apache License v2.0
 * @copyright	2010 Dan Horrigan
 */

/**
 * Change these paths to point to the correct location.
 * The paths are relative to your index.php
 */
$carbon_paths = array(
	'application'	=> './carbon/application',	// The path to the application folder
	'modules'		=> './carbon/modules',		// The path to the modules folder
	'system'		=> './carbon/system'		// The path to the system folder
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
foreach ($carbon_paths as &$folder)
{
	if ( ! is_dir($folder) AND is_dir(DOCROOT.$folder))
	{
		$folder = DOCROOT.$folder;
	}
}

// Define the global path constants
define('APPPATH', realpath($carbon_paths['application']).DIRECTORY_SEPARATOR);
define('MODPATH', realpath($carbon_paths['modules']).DIRECTORY_SEPARATOR);
define('SYSPATH', realpath($carbon_paths['system']).DIRECTORY_SEPARATOR);

// save a bit of memory by unsetting the path array
unset($carbon_paths);

// Load the base, low-level functions
require SYSPATH.'base'.EXT;

// Load in the core class
require SYSPATH.'classes/carbon/core'.EXT;

// If the Carbon class is overrided in the application folder
// load that, else load the core class.
if (is_file(APPPATH.'classes/carbon'.EXT))
{
	require APPPATH.'classes/carbon'.EXT;
}
else
{
	require SYSPATH.'classes/carbon'.EXT;
}

// Initialize the framework
Carbon::init();

$request = Request::instance();
$request->execute();
echo $request->output;

/* End of file index.php */