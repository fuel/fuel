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

class Fuel_Error {
	
	public static $levels = array(
		E_ERROR				=>	'Error',
		E_WARNING			=>	'Warning',
		E_PARSE				=>	'Parsing Error',
		E_NOTICE			=>	'Notice',
		E_CORE_ERROR		=>	'Core Error',
		E_CORE_WARNING		=>	'Core Warning',
		E_COMPILE_ERROR		=>	'Compile Error',
		E_COMPILE_WARNING	=>	'Compile Warning',
		E_USER_ERROR		=>	'User Error',
		E_USER_WARNING		=>	'User Warning',
		E_USER_NOTICE		=>	'User Notice',
		E_STRICT			=>	'Runtime Notice'
	);

	public static $fatal_levels = array(E_PARSE, E_ERROR, E_USER_ERROR, E_COMPILE_ERROR);

	public static $count = 0;

	/**
	 * Native PHP shutdown handler
	 *
	 * @access	private
	 * @param	object	the exception object
	 * @return	string
	 */
	public static function shutdown_handler()
	{
		$last_error = error_get_last();
		
		// Only show valid fatal errors
		if ($last_error AND in_array($last_error['type'], Error::$fatal_levels))
		{
			Error::show_php_error(new ErrorException($last_error['message'], $last_error['type'], 0, $last_error['file'], $last_error['line']));

			exit(1);
		}
	}

	public static function exception_handler(Exception $e)
	{
		Error::show_php_error($e);
	}

	public static function error_handler($severity, $message, $filepath, $line)
	{
		if (($severity & error_reporting()) == $severity)
		{
			Error::show_php_error(new ErrorException($message, $severity, 0, $filepath, $line));
		}
		return true;
	}

	public static function show_php_error(Exception $e)
	{
		Error::$count++;
		$data['type']		= get_class($e);
		$data['severity']	= $e->getCode();
		$data['message']	= $e->getMessage();
		$data['filepath']	= $e->getFile();
		$data['error_line']	= $e->getLine();
		$data['backtrace']	= $e->getTrace();

		if (version_compare(PHP_VERSION, '5.3', '<'))
		{
			for ($i = count($data['backtrace']) - 1; $i > 0; --$i)
			{
				if (isset($data['backtrace'][$i - 1]['args']))
				{
					$data['backtrace'][$i]['args'] = $data['backtrace'][$i - 1]['args'];
					unset($data['backtrace'][$i - 1]['args']);
				}
			}
		}
		array_shift($data['backtrace']);
		
		foreach ($data['backtrace'] as $key => $trace)
		{
			if ( ! isset($trace['file']))
			{
				unset($data['backtrace'][$key]);
			}
			if (strncmp($trace['file'], APPPATH, strlen(APPPATH)) !== 0)
			{
				unset($data['backtrace'][$key]);
			}
		}

		$data['severity'] = ( ! isset(Error::$levels[$data['severity']])) ? $data['severity'] : Error::$levels[$data['severity']];

		$data['debug_lines'] = Debug::file_lines($data['filepath'], $data['error_line']);

		$data['filepath'] = str_replace("\\", "/", $data['filepath']);

		$data['filepath'] = Fuel::clean_path($data['filepath']);

		echo View::factory('errors'.DS.'php_error', $data);
	}


}

/* End of file error.php */