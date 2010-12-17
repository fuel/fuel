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

Autoloader::add_namespaces(array(
	'Fuel\\App'				=> __DIR__.DS.'classes'.DS,
	'Fuel\\App\\Model'		=> __DIR__.DS.'classes'.DS.'model'.DS,
	'Fuel\\App\\Controller'	=> __DIR__.DS.'classes'.DS.'controller'.DS,
));

/* End of file autoload.php */