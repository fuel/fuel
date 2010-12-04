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
	'Fuel'	=>	__DIR__.'/classes/',
));

$loader->add_namespace_alias('Fuel\\Application', 'Fuel');

$loader->add_prefixes(array(
	'Fuel_'		=> COREPATH.'classes/',
));

$loader->add_aliases(array(
	'Arr'			=> 'Fuel\\Application\\Arr',
	'Asset'			=> 'Fuel\\Application\\Asset',
	'Benchmark'		=> 'Fuel\\Application\\Benchmark',

	'Cache'						=> 'Fuel\\Application\\Cache',
	'Cache_Handler_Driver'		=> 'Fuel\\Application\\Cache_Handler_Driver',
	'Cache_Handler_Json'		=> 'Fuel\\Application\\Cache_Handler_Json',
	'Cache_Handler_Serialized'	=> 'Fuel\\Application\\Cache_Handler_Serialized',
	'Cache_Handler_String'		=> 'Fuel\\Application\\Cache_Handler_String',
	'Cache_Storage_Driver'		=> 'Fuel\\Application\\Cache_Storage_Driver',
	'Cache_Storage_File'		=> 'Fuel\\Application\\Cache_Storage_File',

	'Config'		=> 'Fuel\\Application\\Config',

	'Cookie'		=> 'Fuel\\Application\\Cookie',
	'DB'			=> 'Fuel\\Application\\DB',
	'DBUtil'		=> 'Fuel\\Application\\DBUtil',

	'Database'				=> 'Fuel\\Application\\Database',
	'Database_Exception'	=> 'Fuel\\Application\\Database_Exception',

	'Email'				=> 'Fuel\\Application\\Email',
	'Email_Driver'		=> 'Fuel\\Application\\Email_Driver',
	'Email_Mail'		=> 'Fuel\\Application\\Email_Mail',
	'Email_Sendmail'	=> 'Fuel\\Application\\Email_Sendmail',
	'Email_Smtp'		=> 'Fuel\\Application\\Email_Smtp',

	'Date'			=> 'Fuel\\Application\\Date',
	'Debug'			=> 'Fuel\\Application\\Debug',
	'Crypt'			=> 'Fuel\\Application\\Crypt',
	'Env'			=> 'Fuel\\Application\\Env',
	'Event'			=> 'Fuel\\Application\\Event',
	'Error'			=> 'Fuel\\Application\\Error',
	'Form'			=> 'Fuel\\Application\\Form',
	'Ftp'			=> 'Fuel\\Application\\Ftp',
	'Html'			=> 'Fuel\\Application\\Html',
	'Input'			=> 'Fuel\\Application\\Input',
	'Lang'			=> 'Fuel\\Application\\Lang',
	'Log'			=> 'Fuel\\Application\\Log',
	'Migrate'		=> 'Fuel\\Application\\Migrate',
	'Migration'		=> 'Fuel\\Application\\Migration',
	'Model'			=> 'Fuel\\Application\\Model',
	'Output'		=> 'Fuel\\Application\\Output',
	'Pagination'	=> 'Fuel\\Application\\Pagination',
	'Request'		=> 'Fuel\\Application\\Request',
	'Route'			=> 'Fuel\\Application\\Route',

	'Session'			=> 'Fuel\\Application\\Session',
	'Session_Driver'	=> 'Fuel\\Application\\Session_Driver',
	'Session_Db'		=> 'Fuel\\Application\\Session_Db',
	'Session_Cookie'	=> 'Fuel\\Application\\Session_Cookie',
	'Session_File'		=> 'Fuel\\Application\\Session_File',
	'Session_Memcached'	=> 'Fuel\\Application\\Session_Memcached',

	'URI'			=> 'Fuel\\Application\\URI',
	'URL'			=> 'Fuel\\Application\\URL',

	'Validation'		=> 'Fuel\\Application\\Validation',
	'Validation_Object'	=> 'Fuel\\Application\\Validation_Object',
	'Validation_Error'	=> 'Fuel\\Application\\Validation_Error',

	'View'				=> 'Fuel\\Application\\View',
	'View_Exception'	=> 'Fuel\\Application\\View_Exception',

	'Fuel'			=> 'Fuel\\Application\\Fuel',

));

$loader->register();
return $loader;

/* End of file autoload.php */