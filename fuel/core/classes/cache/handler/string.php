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

namespace Fuel;

use Fuel\Application as App;

class Cache_Handler_String implements Cache_Handler_Driver {

	public function readable($contents)
	{
		return (string) $contents;
	}

	public function writable($contents)
	{
		return (string) $contents;
	}
}

/* End of file string.php */