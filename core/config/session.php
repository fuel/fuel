<?php defined('COREPATH') or die('No direct script access.');
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
 * Config information per type:
 * - type => cookie
 * 		- config array is not used
 *
 * - type => file
 * 		- path				path where the session files should be stored.
 * 		- gc_probablility	probability % (between 0 and 100) for garbage collection
 *
 * - type => memcached
 * 		- servers			array with one or more server definitions, each an array with:
 * 			- host			name or IP address of the host that runs the memcached service
 * 			- port			port memcached listens to
 * 			- weight		weight of the server in relation to the total
 * 		( if not defined, one server will be added as default, host = 127.0.0.1, port = 11211 )
 */

return array(
	'type'						=> 'cookie',				// only 'cookie', 'file' and 'memcached' support
	'config'					=> array(					// type specific config settings
									),
	'match_ip'					=> true,					// check for an IP address match after loading the cookie (optional, default = true)
	'match_ua'					=> true,					// check for a user agent match after loading the cookie (optional, default = true)
	'cookie_name'				=> 'fuelsession',			// name of the session cookie  (optional, default = 'fuelsession')
	'cookie_domain' 			=> '',						// cookie domain  (optional, default = '')
	'cookie_path'				=> '/',						// cookie path  (optional, default = '/')
	'expiration_time'			=> 0,						// cookie expiration time, 0 = until browser close  (optional, default = 0)
	'rotation_time'				=> 300,						// session ID rotation time  (optional, default = 300)
	'flash_id'					=> 'flash',					// default ID for flash variables  (optional, default = 'flash')
	'flash_auto_expire'			=> false,					// if FALSE, expire flash values only after it's used  (optional, default = true)
	'write_on_finish'			=> true						// if TRUE, writes are only done once, at the end of a page request
);

/* End of file config/session.php */
