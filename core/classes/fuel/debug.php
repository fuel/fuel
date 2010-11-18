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
	
	protected static $files = array();

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

	/**
	 * Returns the debug lines from the specified file
	 *
	 * @access	protected
	 * @param	string		the file path
	 * @param	int			the line number
	 * @param	bool		whether to use syntax highlighting or not
	 * @param	int			the amount of line padding
	 * @return	array
	 */
	public static function file_lines($filepath, $line_num, $highlight = true, $padding = 5)
	{
		// We cache the entire file to reduce disk IO for multiple errors
		if ( ! isset(Debug::$files[$filepath]))
		{
			Debug::$files[$filepath] = file($filepath, FILE_IGNORE_NEW_LINES);
			array_unshift(Debug::$files[$filepath], '');
		}

		$start = $line_num - $padding;
		if ($start < 0)
		{
			$start = 0;
		}

		$length = ($line_num - $start) + $padding + 1;
		if (($start + $length) > count(Debug::$files[$filepath]) - 1)
		{
			$length = NULL;
		}

		$debug_lines = array_slice(Debug::$files[$filepath], $start, $length, TRUE);
	
		if ($highlight)
		{
			$to_replace = array('<code>', '</code>', '<span style="color: #0000BB">&lt;?php&nbsp;', "\n");
			$replace_with = array('', '', '<span style="color: #0000BB">', '');
	
			foreach ($debug_lines as & $line)
			{
				$line = str_replace($to_replace, $replace_with, highlight_string('<?php ' . $line, TRUE));
			}
		}

		return $debug_lines;
	}
}

/* End of file input.php */