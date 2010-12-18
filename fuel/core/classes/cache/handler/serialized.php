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

namespace Fuel\Core;

use Fuel\App as App;

class Cache_Handler_Serialized implements Cache_Handler_Driver {

	public function readable($contents)
	{
		return unserialize($contents);
	}

	public function writable($contents)
	{
		return serialize($contents);
	}

}

/* End of file serialized.php */
