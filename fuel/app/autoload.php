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

$loader = new Autoloader;
$loader->default_path(__DIR__.'/classes/');

$loader->add_namespaces(array(
	'Fuel\\Application'			=> __DIR__.'/classes/',
	'Fuel\\Application\\Model'	=> __DIR__.'/classes/model/',
));

$loader->register();
return $loader;

/* End of file autoload.php */