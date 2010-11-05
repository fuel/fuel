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

/**
 * Change these paths to point to the correct location.
 * The paths are relative to your index.php
 */
$fuel_paths = array(
	'app'		=> './app',			// The path to the app folder
	'packages'	=> './packages',	// The path to the packages folder
	'core'		=> './core'			// The path to the core folder
);

/**
 * In a production environment you will want to change these 2 settings.
 */
error_reporting(E_ALL);
ini_set('display_errors', true);

/**
 * We disable short open tags by default so as to not confuse people.  They
 * also interfere with generating XML documents.
 */
ini_set('short_open_tag', 0);

/**
 * Do we have access to mbstring?
 * We need this in order to work with UTF-8 strings
 */
define('MBSTRING', function_exists('mb_get_info'));
define('INTERNAL_ENC', 'ISO-8859-1');

// The full path to this file
define('DOCROOT', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);

// Loop through the paths and check if they are absolute or relative.
foreach ($fuel_paths as &$folder)
{
	if ( ! is_dir($folder) and is_dir(DOCROOT.$folder))
	{
		$folder = DOCROOT.$folder;
	}
}

// Define the global path constants
define('APPPATH', realpath($fuel_paths['app']).DIRECTORY_SEPARATOR);
define('PKGPATH', realpath($fuel_paths['packages']).DIRECTORY_SEPARATOR);
define('COREPATH', realpath($fuel_paths['core']).DIRECTORY_SEPARATOR);

// save a bit of memory by unsetting the path array
unset($fuel_paths);

// Load the base, low-level functions
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

/* End of file index.php */