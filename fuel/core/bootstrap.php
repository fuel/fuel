<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2011 Fuel Development Team
 * @link       http://fuelphp.com
 */

// Load the base functions
require COREPATH.'base.php';

// Import the core Fuel class
import('fuel');

// If the app does not have a Fuel class, then we need to alias it.
( ! class_exists('Fuel')) and class_alias('Fuel\\Core\\Fuel', 'Fuel');

define('DS', DIRECTORY_SEPARATOR);
define('CRLF', chr(13).chr(10));

/**
 * Do we have access to mbstring?
 * We need this in order to work with UTF-8 strings
 */
define('MBSTRING', function_exists('mb_get_info'));

/**
 * Is mbstring enabled?
 * Set the encoding to use whatever Fuel is set to use.
 */
MBSTRING and mb_internal_encoding(Fuel::$encoding);

// Is Fuel running on the command line?
Fuel::$is_cli = (bool) defined('STDIN');

// Load in the Autoloader
require COREPATH.'classes'.DS.'autoloader.php';

Autoloader::add_namespace('Fuel\\Core', COREPATH.'classes/');

Autoloader::add_classes(array(
	'Fuel\\Core\\Arr'    => COREPATH.'classes/arr.php',
	'Fuel\\Core\\Asset'  => COREPATH.'classes/asset.php',

	'Fuel\\Core\\Cache'                     => COREPATH.'classes/cache.php',
	'Fuel\\Core\\Cache_Exception'           => COREPATH.'classes/cache/exception.php',
	'Fuel\\Core\\Cache_Handler_Driver'      => COREPATH.'classes/cache/handler/driver.php',
	'Fuel\\Core\\Cache_Handler_Json'        => COREPATH.'classes/cache/handler/json.php',
	'Fuel\\Core\\Cache_Handler_Serialized'  => COREPATH.'classes/cache/handler/serialized.php',
	'Fuel\\Core\\Cache_Handler_String'      => COREPATH.'classes/cache/handler/string.php',
	'Fuel\\Core\\Cache_Storage_Driver'      => COREPATH.'classes/cache/storage/driver.php',
	'Fuel\\Core\\Cache_Storage_File'        => COREPATH.'classes/cache/storage/file.php',
	'Fuel\\Core\\Cache_Storage_Memcached'   => COREPATH.'classes/cache/storage/memcached.php',
	'Fuel\\Core\\Cache_Storage_Redis'       => COREPATH.'classes/cache/storage/redis.php',

	'Fuel\\Core\\Config'               => COREPATH.'classes/config.php',
	'Fuel\\Core\\Controller'           => COREPATH.'classes/controller.php',
	'Fuel\\Core\\Controller_Rest'      => COREPATH.'classes/controller/rest.php',
	'Fuel\\Core\\Controller_Template'  => COREPATH.'classes/controller/template.php',
	'Fuel\\Core\\Cookie'               => COREPATH.'classes/cookie.php',

	'Fuel\\Core\\DB'      => COREPATH.'classes/db.php',
	'Fuel\\Core\\DBUtil'  => COREPATH.'classes/dbutil.php',

	'Fuel\\Core\\Database_Connection'            => COREPATH.'classes/database/connection.php',
	'Fuel\\Core\\Database_Exception'             => COREPATH.'classes/database/exception.php',
	'Fuel\\Core\\Database_Expression'            => COREPATH.'classes/database/expression.php',
	'Fuel\\Core\\Database_Pdo_Connection'        => COREPATH.'classes/database/pdo/connection.php',
	'Fuel\\Core\\Database_Query'                 => COREPATH.'classes/database/query.php',
	'Fuel\\Core\\Database_Query_Builder'         => COREPATH.'classes/database/query/builder.php',
	'Fuel\\Core\\Database_Query_Builder_Insert'  => COREPATH.'classes/database/query/builder/insert.php',
	'Fuel\\Core\\Database_Query_Builder_Delete'  => COREPATH.'classes/database/query/builder/delete.php',
	'Fuel\\Core\\Database_Query_Builder_Update'  => COREPATH.'classes/database/query/builder/update.php',
	'Fuel\\Core\\Database_Query_Builder_Select'  => COREPATH.'classes/database/query/builder/select.php',
	'Fuel\\Core\\Database_Query_Builder_Where'   => COREPATH.'classes/database/query/builder/where.php',
	'Fuel\\Core\\Database_Query_Builder_Join'    => COREPATH.'classes/database/query/builder/join.php',
	'Fuel\\Core\\Database_Result'                => COREPATH.'classes/database/result.php',
	'Fuel\\Core\\Database_Result_Cached'         => COREPATH.'classes/database/result/cached.php',
	'Fuel\\Core\\Database_Mysql_Connection'      => COREPATH.'classes/database/mysql/connection.php',
	'Fuel\\Core\\Database_MySQL_Result'          => COREPATH.'classes/database/mysql/result.php',
	'Fuel\\Core\\Database_Mysqli_Connection'     => COREPATH.'classes/database/mysqli/connection.php',
	'Fuel\\Core\\Database_MySQLi_Result'         => COREPATH.'classes/database/mysqli/result.php',

	'Fuel\\Core\\Email'           => COREPATH.'classes/email.php',
	'Fuel\\Core\\Email_Driver'    => COREPATH.'classes/email/driver.php',
	'Fuel\\Core\\Email_Mail'      => COREPATH.'classes/email/mail.php',
	'Fuel\\Core\\Email_Sendmail'  => COREPATH.'classes/email/sendmail.php',
	'Fuel\\Core\\Email_Smtp'      => COREPATH.'classes/email/smtp.php',

	'Fuel\\Core\\Fuel_Exception'  => COREPATH.'classes/fuel/exception.php',

	'Fuel\\Core\\Date'    => COREPATH.'classes/date.php',
	'Fuel\\Core\\Debug'   => COREPATH.'classes/debug.php',
	'Fuel\\Core\\Cli'     => COREPATH.'classes/cli.php',
	'Fuel\\Core\\Crypt'   => COREPATH.'classes/crypt.php',
	'Fuel\\Core\\Event'   => COREPATH.'classes/event.php',
	'Fuel\\Core\\Error'   => COREPATH.'classes/error.php',
	'Fuel\\Core\\Format'  => COREPATH.'classes/format.php',

	'Fuel\\Core\\Fieldset'        => COREPATH.'classes/fieldset.php',
	'Fuel\\Core\\Fieldset_Field'  => COREPATH.'classes/fieldset/field.php',

	'Fuel\\Core\\File'                   => COREPATH.'classes/file.php',
	'Fuel\\Core\\File_Area'              => COREPATH.'classes/file/area.php',
	'Fuel\\Core\\File_Exception'         => COREPATH.'classes/file/exception.php',
	'Fuel\\Core\\File_Driver_File'       => COREPATH.'classes/file/driver/file.php',
	'Fuel\\Core\\File_Driver_Directory'  => COREPATH.'classes/file/driver/directory.php',

	'Fuel\\Core\\Form'						=> COREPATH.'classes/form.php',
	'Fuel\\Core\\Ftp'						=> COREPATH.'classes/ftp.php',
	'Fuel\\Core\\Html'						=> COREPATH.'classes/html.php',

	'Fuel\\Core\\Image'					=> COREPATH.'classes/image.php',
	'Fuel\\Core\\Image_Driver'			=> COREPATH.'classes/image/driver.php',
	'Fuel\\Core\\Image_Gd'				=> COREPATH.'classes/image/gd.php',
	'Fuel\\Core\\Image_Imagemagick'		=> COREPATH.'classes/image/imagemagick.php',

	'Fuel\\Core\\Inflector'					=> COREPATH.'classes/inflector.php',
	'Fuel\\Core\\Input'						=> COREPATH.'classes/input.php',
	'Fuel\\Core\\Lang'						=> COREPATH.'classes/lang.php',
	'Fuel\\Core\\Log'						=> COREPATH.'classes/log.php',
	'Fuel\\Core\\Migrate'					=> COREPATH.'classes/migrate.php',
	'Fuel\\Core\\Model'						=> COREPATH.'classes/model.php',
	'Fuel\\Core\\Output'					=> COREPATH.'classes/output.php',
	'Fuel\\Core\\Pagination'				=> COREPATH.'classes/pagination.php',
	'Fuel\\Core\\Profiler'					=> COREPATH.'classes/profiler.php',
	'Fuel\\Core\\Request'					=> COREPATH.'classes/request.php',
	'Fuel\\Core\\Response'					=> COREPATH.'classes/response.php',
	'Fuel\\Core\\Route'						=> COREPATH.'classes/route.php',
	'Fuel\\Core\\Router'					=> COREPATH.'classes/router.php',
	'Fuel\\Core\\Security'					=> COREPATH.'classes/security.php',

	'Fuel\\Core\\Session'            => COREPATH.'classes/session.php',
	'Fuel\\Core\\Session_Driver'     => COREPATH.'classes/session/driver.php',
	'Fuel\\Core\\Session_Db'         => COREPATH.'classes/session/db.php',
	'Fuel\\Core\\Session_Cookie'     => COREPATH.'classes/session/cookie.php',
	'Fuel\\Core\\Session_File'       => COREPATH.'classes/session/file.php',
	'Fuel\\Core\\Session_Memcached'  => COREPATH.'classes/session/memcached.php',
	'Fuel\\Core\\Session_Redis'      => COREPATH.'classes/session/redis.php',

	'Fuel\\Core\\Str'  => COREPATH.'classes/str.php',

	'Fuel\\Core\\TestCase'  => COREPATH.'classes/testcase.php',

	'Fuel\\Core\\Uri'     => COREPATH.'classes/uri.php',
	'Fuel\\Core\\Unzip'     => COREPATH.'classes/unzip.php',
	'Fuel\\Core\\Upload'  => COREPATH.'classes/upload.php',

	'Fuel\\Core\\Validation'        => COREPATH.'classes/validation.php',
	'Fuel\\Core\\Validation_Error'  => COREPATH.'classes/validation/error.php',

	'Fuel\\Core\\View'            => COREPATH.'classes/view.php',
	'Fuel\\Core\\View_Exception'  => COREPATH.'classes/view/exception.php',

	'Fuel\\Core\\ViewModel'  => COREPATH.'classes/viewmodel.php',
));

/* End of file bootstrap.php */
