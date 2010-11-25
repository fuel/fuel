<?php
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

return array(
	// global defaults
	'default'			=> 'cookie',					// if no session type is requested, use the default
	'match_ip'			=> false,						// check for an IP address match after loading the cookie (optional, default = false)
	'match_ua'			=> true,						// check for a user agent match after loading the cookie (optional, default = true)
	'cookie_name'		=> 'fuelid',					// name of the session cookie  (optional, default = 'fuelid')
	'cookie_domain' 	=> '',							// cookie domain  (optional, default = '')
	'cookie_path'		=> '/',							// cookie path  (optional, default = '/')
	'expire_on_close'	=> true,						// if true, the session expires when the browser is closed (optional, default = false)
	'expiration_time'	=> 7200,						// session expiration time, <= 0 means 2 years! (optional, default = 2 hours)
	'rotation_time'		=> 300,							// session ID rotation time  (optional, default = 300)
	'flash_id'			=> 'flash',						// default ID for flash variables  (optional, default = 'flash')
	'flash_auto_expire'	=> false,						// if false, expire flash values only after it's used  (optional, default = true)
	'write_on_finish'	=> true,						// if true, writes are only done once, at the end of a page request (optional, default = false)

	// special configuration settings for cookie based sessions
	'cookie'			=> array(
		'cookie_name'		=> 'cookieid',				// you can override any global config value at driver level
						),

	// special configuration settings for file based sessions
	'file'				=> array(
		'path'				=>	'/tmp',					// path where the session files should be stored
		'gc_probability'	=>	5						// probability % (between 0 and 100) for garbage collection
						),

	// special configuration settings for memcached based sessions
	'memcached'			=> array(
		'servers'			=> array(					// array of servers and portnumbers that run the memcached service
								array('host' => '127.0.0.1', 'port' => 11211, 'weight' => 100)
							),
						),

	// special configuration settings for database based sessions
	'db'			=> array(
		'database'			=>	'dev',					// name of the database config
		'table'				=>	'sessions',				// name of the sessions table
		'gc_probability'	=>	5						// probability % (between 0 and 100) for garbage collection
						)
);

/* End of file config/session.php */
