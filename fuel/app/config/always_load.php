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

namespace Fuel\Application;

return array(

	/**
	 * Classes to autoload & initialize even when not used
	 */
	'classes'	=> array(),

	/**
	 * Configs to autoload
	 *
	 * Examples: if you want to load 'session' config into a group 'session' you only have to
	 * add 'session'. If you want to add it to another group (example: 'auth') you have to
	 * add it like 'session' => 'auth'.
	 * If you don't want the config in a group use null as groupname.
	 */
	'config'	=> array(),

	/**
	 * Language files to autoload
	 *
	 * Examples: if you want to load 'validation' lang into a group 'validation' you only have to
	 * add 'validation'. If you want to add it to another group (example: 'forms') you have to
	 * add it like 'validation' => 'forms'.
	 * If you don't want the lang in a group use null as groupname. 
	 */
	'language'	=> array(),
);

/* End of file config.php */