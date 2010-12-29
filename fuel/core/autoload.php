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
	'Fuel\\Core'	=>	__DIR__.DS.'classes'.DS,
));

Autoloader::add_namespace_aliases(array(
	'Fuel\\App' => 'Fuel\\Core',
	'Fuel\\App\\Model' => 'Fuel\\App',
	'Fuel\\App\\Controller' => 'Fuel\\App',
));

Autoloader::add_aliases(array(
	'Autoloader'				=> 'Fuel\\App\\Autoloader',
	'Fuel\\Autoloader'			=> 'Fuel\\App\\Autoloader',
	'Arr'						=> 'Fuel\\App\\Arr',
	'Asset'						=> 'Fuel\\App\\Asset',

	'Cache'						=> 'Fuel\\App\\Cache',
	'Cache_Handler_Driver'		=> 'Fuel\\App\\Cache_Handler_Driver',
	'Cache_Handler_Json'		=> 'Fuel\\App\\Cache_Handler_Json',
	'Cache_Handler_Serialized'	=> 'Fuel\\App\\Cache_Handler_Serialized',
	'Cache_Handler_String'		=> 'Fuel\\App\\Cache_Handler_String',
	'Cache_Storage_Driver'		=> 'Fuel\\App\\Cache_Storage_Driver',
	'Cache_Storage_File'		=> 'Fuel\\App\\Cache_Storage_File',
	'Cache_Storage_Memcached'	=> 'Fuel\\App\\Cache_Storage_Memcached',
	'Cache_Storage_Redis'		=> 'Fuel\\App\\Cache_Storage_Redis',

	'Config'					=> 'Fuel\\App\\Config',
	'Cookie'					=> 'Fuel\\App\\Cookie',

	'DB'						=> 'Fuel\\App\\DB',
	'DBUtil'					=> 'Fuel\\App\\DBUtil',

	'Database'					=> 'Fuel\\App\\Database',
	'Database_Exception'		=> 'Fuel\\App\\Database_Exception',

	'Email'						=> 'Fuel\\App\\Email',
	'Email_Driver'				=> 'Fuel\\App\\Email_Driver',
	'Email_Mail'				=> 'Fuel\\App\\Email_Mail',
	'Email_Sendmail'			=> 'Fuel\\App\\Email_Sendmail',
	'Email_Smtp'				=> 'Fuel\\App\\Email_Smtp',

	'Date'						=> 'Fuel\\App\\Date',
	'Debug'						=> 'Fuel\\App\\Debug',
	'Crypt'						=> 'Fuel\\App\\Crypt',
	'Env'						=> 'Fuel\\App\\Env',
	'Event'						=> 'Fuel\\App\\Event',
	'Error'						=> 'Fuel\\App\\Error',
	'Form'						=> 'Fuel\\App\\Form',
	'Ftp'						=> 'Fuel\\App\\Ftp',
	'Html'						=> 'Fuel\\App\\Html',
	'Input'						=> 'Fuel\\App\\Input',
	'Lang'						=> 'Fuel\\App\\Lang',
	'Log'						=> 'Fuel\\App\\Log',
	'Migrate'					=> 'Fuel\\App\\Migrate',
	'Migration'					=> 'Fuel\\App\\Migration',
	'Model'						=> 'Fuel\\App\\Model',
	'Output'					=> 'Fuel\\App\\Output',
	'Pagination'				=> 'Fuel\\App\\Pagination',
	'Profiler'					=> 'Fuel\\App\\Profiler',
	'Request'					=> 'Fuel\\App\\Request',
	'Route'						=> 'Fuel\\App\\Route',

	'Session'					=> 'Fuel\\App\\Session',
	'Session_Driver'			=> 'Fuel\\App\\Session_Driver',
	'Session_Db'				=> 'Fuel\\App\\Session_Db',
	'Session_Cookie'			=> 'Fuel\\App\\Session_Cookie',
	'Session_File'				=> 'Fuel\\App\\Session_File',
	'Session_Memcached'			=> 'Fuel\\App\\Session_Memcached',
	'Session_Redis'				=> 'Fuel\\App\\Session_Redis',

	'Uri'						=> 'Fuel\\App\\Uri',
	'Upload'					=> 'Fuel\\App\\Upload',

	'Validation'				=> 'Fuel\\App\\Validation',
	'Validation_Set'			=> 'Fuel\\App\\Validation_Set',
	'Validation_Field'			=> 'Fuel\\App\\Validation_Field',
	'Validation_Error'			=> 'Fuel\\App\\Validation_Error',

	'View'						=> 'Fuel\\App\\View',
	'View_Exception'			=> 'Fuel\\App\\View_Exception',

	'Fuel'					=> 'Fuel\\App\\Fuel',

));

/* End of file autoload.php */
