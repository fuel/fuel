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


Autoloader::add_path(__DIR__.DS.'classes'.DS);

Autoloader::add_namespaces(array(
	'Fuel\\Application'				=> __DIR__.DS.'classes'.DS,
	'Fuel\\Application\\Model'		=> __DIR__.DS.'classes/model'.DS,
	'Fuel\\Application\\Controller'	=> __DIR__.DS.'classes/controller'.DS,
));

/* End of file autoload.php */