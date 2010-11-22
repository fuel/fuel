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

$loader->default_path(dirname(__FILE__).'/classes/');

$loader->add_aliases(array(
	'DB'				=> 'Fuel_DB',
	'DB_Driver'			=> array('Fuel_DB_Driver', true),
	'DB_Query'			=> 'Fuel_DB_Query',
	'DB_Result'			=> array('Fuel_DB_Result', true),
	'DB_Mysql_Driver'	=> 'Fuel_DB_Mysql_Driver',
	'DB_Mysql_Result'	=> 'Fuel_DB_Mysql_Result',
));

$loader->register();
return $loader;

/* End of file autoload.php */