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

namespace Fuel\Application;


Autoloader::add_path(__DIR__.'/classes/');

Autoloader::add_namespaces(array(
	'Fuel\\Application'				=> __DIR__.'/classes/',
	'Fuel\\Application\\Model'		=> __DIR__.'/classes/model/',
	'Fuel\\Application\\Controller'	=> __DIR__.'/classes/controller/',
));

/* End of file autoload.php */