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

return array(

	/**
	 * ----------------------------------------------------------------------
	 * global settings
	 * ----------------------------------------------------------------------
	 */

	// default storage driver
	'driver'				=> 'memcached',

	// default expiration (null = no expiration)
	'expiration'	=> null,

	/**
	 * Default content handlers: convert values to strings to be stored
	 * You can set them per primitive type or object class like this:
	 *   - 'string_handler' 		=> 'string'
	 *   - 'array_handler'			=> 'json'
	 *   - 'Some_Object_handler'	=> 'serialize'
	 */

	/**
	 * ----------------------------------------------------------------------
	 * storage driver settings
	 * ----------------------------------------------------------------------
	 */

	// specific configuration settings for the file driver
	'file'					=> array(
		'path'					=>	'',					// if empty the default will be application/cache/
							),

	// specific configuration settings for the memcached driver
	'memcached'				=> array(
		'cache_id'				=> 'fuel',					// unique id to distinquish fuel cache items from others stored on the same server(s)
		'servers'				=> array(					// array of servers and portnumbers that run the memcached service
									array('host' => '127.0.0.1', 'port' => 11211, 'weight' => 100)
								),
							),

	// specific configuration settings for the redis driver
	'redis'					=> array(
		'database'				=> 'default'				// name of the redis database to use (as configured in config/db.php)
							)

);

/* End of file cache.php */
