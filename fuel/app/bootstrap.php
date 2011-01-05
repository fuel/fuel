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
 * @copyright	2010 - 2011 Fuel Development Team
 * @link		http://fuelphp.com
 */

if (DEBUG)
{
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
}


// Register the autoloader
Fuel\Core\Autoloader::register();

// Initialize the framework with the config file.
Fuel::init(include(APPPATH.'config/config.php'));

// Generate the request, execute it and send the output.
Request::factory()->execute()->send_headers()->output();

Event::shutdown();
Fuel::finish();


/* End of file bootstrap.php */