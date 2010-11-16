<?php defined('COREPATH') or die('No direct script access.');
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

interface Fuel_Cache_Handler_Driver {

	/**
	 * Should make the contents readable
	 *
	 * @access	public
	 * @param	mixed
	 * @return	mixed
	 */
	public function readable($contents);

	/**
	 * Should make the contents writable
	 *
	 * @access	public
	 * @param	mixed
	 * @return	mixed
	 */
	public function writable($contents);
}

/* End of file driver.php */