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

$loader->default_path(dirname(__FILE__).'/classes/');

$loader->add_namespaces(array(
	'Fuel'	=>	__DIR__.'/classes/',
));

$loader->add_prefixes(array(
	'Fuel_'		=> COREPATH.'classes/',
));

$loader->add_aliases(array(
	'Arr'			=> 'Fuel_Arr',
	'Asset'			=> 'Fuel_Asset',
	'Benchmark'		=> 'Fuel_Benchmark',

	'Cache'						=> 'Fuel_Cache',
	'Cache_Handler_Driver'		=> 'Fuel_Cache_Handler_Driver',
	'Cache_Handler_Json'		=> 'Fuel_Cache_Handler_Json',
	'Cache_Handler_Serialized'	=> 'Fuel_Cache_Handler_Serialized',
	'Cache_Handler_String'		=> 'Fuel_Cache_Handler_String',
	'Cache_Storage_Driver'		=> 'Fuel_Cache_Storage_Driver',
	'Cache_Storage_File'		=> 'Fuel_Cache_Storage_File',

	'Config'		=> 'Fuel_Config',

	'Cookie'		=> 'Fuel_Cookie',
	'Debug'			=> 'Fuel_Debug',
	'Encrypt'		=> 'Fuel_Encrypt',
	'Env'			=> 'Fuel_Env',
	'Event'			=> 'Fuel_Event',
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

	'Session'					=> 'Fuel_Session',
	'Session_Driver'			=> 'Fuel_Session_Driver',
	'Session_Exception'			=> 'Fuel_Session_Exception',
	'Session_Cookie_Driver'		=> 'Fuel_Session_Cookie_Driver',
	'Session_File_Driver'		=> 'Fuel_Session_File_Driver',
	'Session_Memcached_Driver'	=> 'Fuel_Session_Memcached_Driver',

	'URI'			=> 'Fuel_URI',
	'URL'			=> 'Fuel_URL',

	'View'				=> 'Fuel_View',
	'View_Exception'	=> 'Fuel_View_Exception',

	'Fuel'			=> 'Fuel_Core',
));

$loader->register();
return $loader;

/* End of file autoload.php */