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


// Initialize the framework
Fuel::init();

// Generate the request, execute it and send the output.
Request::factory()->execute()->send_headers()->output();

Event::shutdown();
Fuel::finish();


/* End of file bootstrap.php */