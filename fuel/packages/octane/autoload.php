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

namespace Fuel\App;

Autoloader::add_path(__DIR__.'/classes/');

Autoloader::add_namespaces(array(
	'Fuel\\Octane'		=> __DIR__.'/classes/',
));

Autoloader::add_namespace_aliases(array(
	'Fuel\\Octane\\Test'	=> 'Fuel\\App',
));

/* End of file autoload.php */