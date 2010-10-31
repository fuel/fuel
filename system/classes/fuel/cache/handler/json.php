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

class Fuel_Cache_Handler_Json implements Fuel_Cache_Handler_Driver {

	public function readable($contents)
	{
		return json_decode($contents);
	}

	public function writable($contents)
	{
		return json_encode($contents);
	}
}

/* End of file json.php */