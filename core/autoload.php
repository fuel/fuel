<?php defined('COREPATH') or die('No direct script access.');
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
$loader->add_packages(array(
	'Fuel_'		=> COREPATH.'classes/',
));

$loader->add_aliases(array(
	'Arr'			=> 'Fuel_Arr',
	'Asset'			=> 'Fuel_Asset',
	'Benchmark'		=> 'Fuel_Benchmark',
	'Cache'			=> 'Fuel_Cache',
	'Config'		=> 'Fuel_Config',
	'Controller'	=> 'Fuel_Controller',
	'Cookie'		=> 'Fuel_Cookie',
	'DB'			=> 'Fuel_DB',
	'Debug'			=> 'Fuel_Debug',
	'Encrypt'		=> 'Fuel_Encrypt',
	'Env'			=> 'Fuel_Env',
	'Error'			=> 'Fuel_Error',
	'Form'			=> 'Fuel_Form',
	'Ftp'			=> 'Fuel_Ftp',
	'Input'			=> 'Fuel_Input',
	'Lang'			=> 'Fuel_Lang',
	'Log'			=> 'Fuel_Log',
	'Migrate'		=> 'Fuel_Migrate',
	'Model'			=> 'Fuel_Model',
	'Output'		=> 'Fuel_Output',
	'Request'		=> 'Fuel_Request',
	'Route'			=> 'Fuel_Route',
	'Session'		=> 'Fuel_Session',
	'URI'			=> 'Fuel_URI',
	'URL'			=> 'Fuel_URL',
	'View'			=> 'Fuel_View',
	'Fuel'			=> 'Fuel_Core',
));

$loader->register();

/* End of file autoload.php */