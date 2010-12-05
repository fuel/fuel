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

namespace Cli;

class Exception extends \Fuel\Application\Exception {

	public function  __toString()
	{
		echo get_class($this) . " '{$this->message}' in {$this->file}({$this->line}";
	}

}


/* End of file exception.php */