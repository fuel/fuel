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

return array(
	'type'						=> 'cookie',					// for now, only 'cookie' and 'file' support
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
