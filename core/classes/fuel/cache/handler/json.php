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

class Fuel_Cache_Handler_Json implements Fuel_Cache_Handler_Driver {

	public function readable($contents)
	{
		$array = false;
		if (substr($contents, 0, 1) == 'a')
		{
			$contents = substr($contents, 1);
			$array = true;
		}

		return json_decode($contents, $array);
	}

	public function writable($contents)
	{
		$array = '';
		if (is_array($contents))
		{
			$array = 'a';
		}

		return $array.json_encode($contents);
	}
}

/* End of file json.php */