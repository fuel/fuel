<?php defined('SYSPATH') or die('No direct script access.');
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

return array(

	/**
	 * Default storage engine
	 */
	'storage'				=> 'file',
	
	/**
	 * Default expiration (0 = no expiration)
	 */
	'default_expiration'	=> 0,
	
	/**
	 * Default content handlers: convert values to strings to be stored
	 * You can set them per primitive type or object class like this:
	 *   - 'string_handler' 		=> 'string'
	 *   - 'array_handler'			=> 'json'
	 *   - 'Some_Object_handler'	=> 'serialize'
	 */

	/**
	 * ----------------------------------------------------------------------
	 * File storage settings
	 * ----------------------------------------------------------------------
	 *
	 * If empty the default will be application/cache/
	 *
	 */
	'path'					=> ''

);

/* End of file config.php */