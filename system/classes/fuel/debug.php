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

class Fuel_Debug {
	
	/**
	 * Quick and dirty way to output a mixed variable to the browser
	 *
	 * @author	Phil Sturgeon <http://philsturgeon.co.uk/>
	 * @static
	 * @access	public
	 * @return	string
	 */
	public static function dump()
	{
		list($callee) = debug_backtrace();
		$arguments = $callee['args'];
		$total_arguments = count($arguments);

		echo '<fieldset style="background: #fefefe !important; border:2px red solid; padding:5px; position:relative; z-index: 999;">';
		echo '<legend style="background:lightgrey; padding:5px;">'.$callee['file'].' @ line: '.$callee['line'].'</legend><pre>';

		$i = 0;
		foreach ($arguments as $argument)
		{
			echo '<br/><strong>Debug #'.(++$i).' of '.$total_arguments.'</strong>: ';
			var_dump($argument);
		}

		echo "</pre>";
		echo "</fieldset>";
	}
	
}

/* End of file input.php */