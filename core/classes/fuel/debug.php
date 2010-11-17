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
		$arguments = func_get_args();
		$total_arguments = count($arguments);

		$callee['file'] = str_replace(array(APPPATH, COREPATH), array('APPPATH/', 'COREPATH/'), $callee['file']);

		echo '<div style="background: #EEE !important; border:1px solid #666; padding:10px;">';
		echo '<h1 style="border-bottom: 1px solid #CCC; padding: 0 0 5px 0; margin: 0 0 5px 0; font: bold 18px sans-serif;">'.$callee['file'].' @ line: '.$callee['line'].'</h1><pre>';
		$i = 0;
		foreach ($arguments as $argument)
		{
			echo '<strong>Debug #'.(++$i).' of '.$total_arguments.'</strong>:<br />';
			if (is_array($argument))
			{
				print_r($argument);
			}
			else
			{
				var_dump($argument);
			}
			echo '<br />';
		}

		echo "</pre>";
		echo "</div>";
	}
	
}

/* End of file input.php */