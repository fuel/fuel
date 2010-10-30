<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Harro "WanWizard" Verton
 * @license		MIT License
 * @copyright	2010 Dan Horrigan
 * @link		http://fuelphp.com
 */

/**
 * Config information:
 * - type = cookie
 * 		- config array is not used
 *
 * - type = file
 * 		- path				path where the session files should be stored.
 * 		- gc_probablility	probability % (between 0 and 100) for garbage collection
 *
 * - type = memcached
 * 		- servers			array with one or more server definitions, each an array with:
 * 			- host			name or IP address of the host that runs the memcached service
 * 			- port			port memcached listens to
 * 		  	( if not defined, one server will be added as default, host = 127.0.0.1, port = 11211 )
 */

return array(
	'type'						=> 'memcached',					// for now, only 'cookie' and 'file' support
	'config'					=> array(					// type specific config settings
									),
	'match_ip'					=> TRUE,					// check for an IP address match after loading the cookie
	'match_ua'					=> TRUE,					// check for a user agent match after loading the cookie
	'cookie_name'				=> 'fuelsession',			// name of the session cookie
	'cookie_domain' 			=> '',						// cookie domain
	'cookie_path'				=> '/',						// cookie path
	'expiration_time'			=> 0,						// cookie expiration time, 0 = until browser close
	'rotation_time'				=> 300,						// session ID rotation time
	'flash_id'					=> 'flash',					// default ID for flash variables
	'flash_auto_expire'			=> FALSE					// if FALSE, expire flash values only after it's used
);

/* End of file config/session.php */
