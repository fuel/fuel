<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2011 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Core;

/**
 * Debug class
 *
 * The Debug class is a simple utility for debugging variables, objects, arrays, etc by outputting information to the display.
 *
 * @package		Fuel
 * @category	Core
 * @author		Phil Sturgeon
 * @link		http://fuelphp.com/docs/classes/debug.html
 */
class Debug {

	protected static $js_displayed = false;

	protected static $files = array();

	/**
	 * Quick and nice way to output a mixed variable to the browser
	 *
	 * @static
	 * @access	public
	 * @return	string
	 */
	public static function dump()
	{
		$backtrace = debug_backtrace();

		// If being called from within, show the file above in the backtrack
		if (strpos($backtrace[0]['file'], 'core/classes/debug.php') !== FALSE)
		{
			$callee = $backtrace[1];
			$label = \Inflector::humanize($backtrace[1]['function']);
		}
		else
		{
			$callee = $backtrace[0];
			$label = 'Debug';
		}

		$arguments = func_get_args();
		$total_arguments = count($arguments);

		$callee['file'] = \Fuel::clean_path($callee['file']);

		echo '<div style="font-size: 13px;background: #EEE !important; border:1px solid #666; color: #000 !important; padding:10px;">';
		echo '<h1 style="border-bottom: 1px solid #CCC; padding: 0 0 5px 0; margin: 0 0 5px 0; font: bold 120% sans-serif;">'.$callee['file'].' @ line: '.$callee['line'].'</h1>';
		echo '<pre style="overflow:auto;font-size:100%;">';

		$count = count($arguments);
		for ($i = 1; $i <= $count; $i++)
		{
			echo '<strong>Variable #'.$i.':</strong>'.PHP_EOL;
			var_dump($arguments[$i - 1]);
			echo PHP_EOL.PHP_EOL;
		}

		echo "</pre>";
		echo "</div>";
	}

	/**
	 * Quick and nice way to output a mixed variable to the browser
	 *
	 * @static
	 * @access	public
	 * @return	string
	 */
	public static function inspect()
	{
		$backtrace = debug_backtrace();

		// If being called from within, show the file above in the backtrack
		if (strpos($backtrace[0]['file'], 'core/classes/debug.php') !== FALSE)
		{
			$callee = $backtrace[1];
			$label = \Inflector::humanize($backtrace[1]['function']);
		}
		else
		{
			$callee = $backtrace[0];
			$label = 'Debug';
		}

		$arguments = func_get_args();
		$total_arguments = count($arguments);

		$callee['file'] = \Fuel::clean_path($callee['file']);

		if ( ! static::$js_displayed)
		{
			echo <<<JS
<script type="text/javascript">function fuel_debug_toggle(a){if(document.getElementById){if(document.getElementById(a).style.display=="none"){document.getElementById(a).style.display="block"}else{document.getElementById(a).style.display="none"}}else{if(document.layers){if(document.id.display=="none"){document.id.display="block"}else{document.id.display="none"}}else{if(document.all.id.style.display=="none"){document.all.id.style.display="block"}else{document.all.id.style.display="none"}}}};</script>
JS;
			static::$js_displayed = true;
		}
		echo '<div style="font-size: 13px;background: #EEE !important; border:1px solid #666; color: #000 !important; padding:10px;">';
		echo '<h1 style="border-bottom: 1px solid #CCC; padding: 0 0 5px 0; margin: 0 0 5px 0; font: bold 120% sans-serif;">'.$callee['file'].' @ line: '.$callee['line'].'</h1>';
		echo '<pre style="overflow:auto;font-size:100%;">';
		$i = 0;
		foreach ($arguments as $argument)
		{
			echo '<strong>'.$label.' #'.(++$i).' of '.$total_arguments.'</strong>:<br />';
				echo static::format('...', $argument);
			echo '<br />';
		}

		echo "</pre>";
		echo "</div>";
	}

	/**
	 * Formats the given $var's output in a nice looking, Foldable interface.
	 *
	 * @param	string	$name	the name of the var
	 * @param	mixed	$var	the variable
	 * @param	int		$level	the indentation level
	 * @param	string	$indent_char	the indentation character
	 * @return	string	the formatted string.
	 */
	public static function format($name, $var, $level = 0, $indent_char = '&nbsp;&nbsp;&nbsp;&nbsp;')
	{
		$return = str_repeat($indent_char, $level);
		if (is_array($var))
		{
			$id = 'fuel_debug_'.mt_rand();
			if (count($var) > 0)
			{
				$return .= "<a href=\"javascript:fuel_debug_toggle('$id');\"><strong>{$name}</strong></a>";
			}
			else
			{
				$return .= "<strong>{$name}</strong>";
			}
			$return .=  " (Array, ".count($var)." elements)\n";

			$sub_return = '';
			foreach ($var as $key => $val)
			{
				$sub_return .= static::format($key, $val, $level + 1);
			}

			if (count($var) > 0)
			{
				$return .= "<span id=\"$id\" style=\"display: none;\">$sub_return</span>";
			}
			else
			{
				$return .= $sub_return;
			}
		}
		elseif (is_string($var))
		{
			$return .= "<strong>{$name}</strong> (String, ".strlen($var)." characters): \"{$var}\"\n";
		}
		elseif (is_float($var))
		{
			$return .= "<strong>{$name}</strong> (Float): {$var}\n";
		}
		elseif (is_long($var))
		{
			$return .= "<strong>{$name}</strong> (Integer): {$var}\n";
		}
		elseif (is_null($var))
		{
			$return .= "<strong>{$name}</strong> (Null): null\n";
		}
		elseif (is_bool($var))
		{
			$return .= "<strong>{$name}</strong> (Boolean): ".($var ? 'true' : 'false')."\n";
		}
		elseif (is_double($var))
		{
			$return .= "<strong>{$name}</strong> (Double): {$var}\n";
		}
		elseif (is_object($var))
		{
			$id = 'fuel_debug_'.mt_rand();
			$vars = get_object_vars($var);
			if (count($vars) > 0)
			{
				$return .= "<a href=\"javascript:fuel_debug_toggle('$id');\"><strong>{$name}</strong></a>";
			}
			else
			{
				$return .= "<strong>{$name}</strong>";
			}
			$return .= " (Object): ".get_class($var)."\n";

			$sub_return = '';
			foreach ($vars as $key => $val)
			{
				$sub_return .= static::format($key, $val, $level + 1);
			}

			if (count($vars) > 0)
			{
				$return .= "<span id=\"$id\" style=\"display: none;\">$sub_return</span>";
			}
			else
			{
				$return .= $sub_return;
			}
		}
		else
		{
			$return .= "<strong>{$name}</strong>: {$var}\n";
		}
		return $return;
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
		if ( ! isset(static::$files[$filepath]))
		{
			static::$files[$filepath] = file($filepath, FILE_IGNORE_NEW_LINES);
			array_unshift(static::$files[$filepath], '');
		}

		$start = $line_num - $padding;
		if ($start < 0)
		{
			$start = 0;
		}

		$length = ($line_num - $start) + $padding + 1;
		if (($start + $length) > count(static::$files[$filepath]) - 1)
		{
			$length = NULL;
		}

		$debug_lines = array_slice(static::$files[$filepath], $start, $length, TRUE);

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

	public static function backtrace()
	{
		return static::dump(debug_backtrace());
	}

	/**
	* Prints a list of all currently declared classes.
	*
	* @access public
	* @static
	*/
	public static function classes()
	{
		return static::dump(get_declared_classes());
	}

	/**
	* Prints a list of all currently declared interfaces (PHP5 only).
	*
	* @access public
	* @static
	*/
	public static function interfaces()
	{
		return static::dump(get_declared_interfaces());
	}

	/**
	* Prints a list of all currently included (or required) files.
	*
	* @access public
	* @static
	*/
	public static function includes()
	{
	return static::dump(get_included_files());
	}

	/**
	 * Prints a list of all currently declared functions.
	 *
	 * @access public
	 * @static
	 */
	public static function functions()
	{
		return static::dump(get_defined_functions());
	}

	/**
	 * Prints a list of all currently declared constants.
	 *
	 * @access public
	 * @static
	 */
	public static function constants()
	{
		return static::dump(get_defined_constants());
	}

	/**
	 * Prints a list of all currently loaded PHP extensions.
	 *
	 * @access public
	 * @static
	 */
	public static function extensions()
	{
		return static::dump(get_loaded_extensions());
	}

	/**
	 * Prints a list of all HTTP request headers.
	 *
	 * @access public
	 * @static
	 */
	public static function headers()
	{
		return static::dump(getAllHeaders());
	}

	/**
	 * Prints a list of the configuration settings read from <i>php.ini</i>
	 *
	 * @access public
	 * @static
	 */
	public static function phpini()
	{
		if ( ! is_readable(get_cfg_var('cfg_file_path')))
		{
			return false;
		}

		// render it
		return static::dump(parse_ini_file(get_cfg_var('cfg_file_path'), true));
	}

}

/* End of file debug.php */