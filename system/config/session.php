<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Dan Horrigan <http://dhorrigan.com>
 * @license		Apache License v2.0
 * @copyright	2010 Dan Horrigan
 */

return array(
	'type'			=> 'cookie',
	'config'		=> array(),
	'expiration'	=> 0,
	'match_ip'		=> TRUE,
	'match_ua'		=> TRUE,
	'cookie_name'	=> 'fuelsession',
	'cookie_domain' => '',
	'cookie_path'	=> '/',
	'rotation'		=> 30,
	'flash_id'		=> 'flash'
);

/* End of file config/session.php */
