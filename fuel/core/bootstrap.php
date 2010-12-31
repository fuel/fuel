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

Fuel\Core\Autoloader::add_classes(array(
	'Fuel\\Core\\Arr'						=> COREPATH.'classes/arr.php',
	'Fuel\\Core\\Asset'						=> COREPATH.'classes/asset.php',

	'Fuel\\Core\\Cache'						=> COREPATH.'classes/cache.php',
	'Fuel\\Core\\Cache_Handler_Driver'		=> COREPATH.'classes/cache/handler/driver.php',
	'Fuel\\Core\\Cache_Handler_Json'		=> COREPATH.'classes/cache/handler/json.php',
	'Fuel\\Core\\Cache_Handler_Serialized'	=> COREPATH.'classes/cache/handler/serialized.php',
	'Fuel\\Core\\Cache_Handler_String'		=> COREPATH.'classes/cache/handler/string.php',
	'Fuel\\Core\\Cache_Storage_Driver'		=> COREPATH.'classes/cache/storage/driver.php',
	'Fuel\\Core\\Cache_Storage_File'		=> COREPATH.'classes/cache/storage/file.php',
	'Fuel\\Core\\Cache_Storage_Memcached'	=> COREPATH.'classes/cache/storage/memcached.php',
	'Fuel\\Core\\Cache_Storage_Redis'		=> COREPATH.'classes/cache/storage/redis.php',

	'Fuel\\Core\\Config'					=> COREPATH.'classes/config.php',
	'Fuel\\Core\\Controller'				=> COREPATH.'classes/controller.php',
	'Fuel\\Core\\Controller_Rest'			=> COREPATH.'classes/controller/rest.php',
	'Fuel\\Core\\Controller_Template'		=> COREPATH.'classes/controller/template.php',
	'Fuel\\Core\\Cookie'					=> COREPATH.'classes/cookie.php',

	'Fuel\\Core\\DB'						=> COREPATH.'classes/db.php',
	'Fuel\\Core\\DBUtil'					=> COREPATH.'classes/dbtil.php',

	'Fuel\\Core\\Database'					=> COREPATH.'classes/database.php',
	'Fuel\\Core\\Database_Exception'		=> COREPATH.'classes/database/exception.php',

	'Fuel\\Core\\Email'						=> COREPATH.'classes/email.php',
	'Fuel\\Core\\Email_Driver'				=> COREPATH.'classes/email/driver.php',
	'Fuel\\Core\\Email_Mail'				=> COREPATH.'classes/email/mail.php',
	'Fuel\\Core\\Email_Sendmail'			=> COREPATH.'classes/email/sendmail.php',
	'Fuel\\Core\\Email_Smtp'				=> COREPATH.'classes/email/smtp.php',

	'Fuel\\Core\\Exception'					=> COREPATH.'classes/exception.php',

	'Fuel\\Core\\Date'						=> COREPATH.'classes/date.php',
	'Fuel\\Core\\Debug'						=> COREPATH.'classes/debug.php',
	'Fuel\\Core\\Cli'						=> COREPATH.'classes/cli.php',
	'Fuel\\Core\\Crypt'						=> COREPATH.'classes/crypt.php',
	'Fuel\\Core\\Event'						=> COREPATH.'classes/event.php',
	'Fuel\\Core\\Error'						=> COREPATH.'classes/error.php',
	'Fuel\\Core\\Form'						=> COREPATH.'classes/form.php',
	'Fuel\\Core\\Ftp'						=> COREPATH.'classes/ftp.php',
	'Fuel\\Core\\Html'						=> COREPATH.'classes/html.php',
	'Fuel\\Core\\Inflector'					=> COREPATH.'classes/inflector.php',
	'Fuel\\Core\\Input'						=> COREPATH.'classes/input.php',
	'Fuel\\Core\\Lang'						=> COREPATH.'classes/lang.php',
	'Fuel\\Core\\Log'						=> COREPATH.'classes/log.php',
	'Fuel\\Core\\Migrate'					=> COREPATH.'classes/migrate.php',
	'Fuel\\Core\\Migration'					=> COREPATH.'classes/migration.php',
	'Fuel\\Core\\Model'						=> COREPATH.'classes/model.php',
	'Fuel\\Core\\Output'					=> COREPATH.'classes/output.php',
	'Fuel\\Core\\Pagination'				=> COREPATH.'classes/pagination.php',
	'Fuel\\Core\\Profiler'					=> COREPATH.'classes/profiler.php',
	'Fuel\\Core\\Request'					=> COREPATH.'classes/request.php',
	'Fuel\\Core\\Route'						=> COREPATH.'classes/route.php',
	'Fuel\\Core\\Security'					=> COREPATH.'classes/security.php',

	'Fuel\\Core\\Session'					=> COREPATH.'classes/session.php',
	'Fuel\\Core\\Session_Driver'			=> COREPATH.'classes/session/driver.php',
	'Fuel\\Core\\Session_Db'				=> COREPATH.'classes/session/db.php',
	'Fuel\\Core\\Session_Cookie'			=> COREPATH.'classes/session/cookie.php',
	'Fuel\\Core\\Session_File'				=> COREPATH.'classes/session/file.php',
	'Fuel\\Core\\Session_Memcached'			=> COREPATH.'classes/session/memcached.php',
	'Fuel\\Core\\Session_Redis'				=> COREPATH.'classes/session/redis.php',

	'Fuel\\Core\\Uri'						=> COREPATH.'classes/uri.php',
	'Fuel\\Core\\Upload'					=> COREPATH.'classes/upload.php',

	'Fuel\\Core\\Validation'				=> COREPATH.'classes/validation.php',
	'Fuel\\Core\\Validation_Set'			=> COREPATH.'classes/validation/set.php',
	'Fuel\\Core\\Validation_Field'			=> COREPATH.'classes/validation/field.php',
	'Fuel\\Core\\Validation_Error'			=> COREPATH.'classes/validation/error.php',

	'Fuel\\Core\\View'						=> COREPATH.'classes/view.php',
	'Fuel\\Core\\View_Exception'			=> COREPATH.'classes/view/exception.php',
));

/* End of file bootstrap.php */