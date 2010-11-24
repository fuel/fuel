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

class Error {
	
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
		if ($last_error AND in_array($last_error['type'], static::$fatal_levels))
		{
			$severity = static::$levels[$last_error['type']];
			Log::error($severity.' - '.$last_error['message'].' in '.$last_error['file'].' on line '.$last_error['line']);

			static::show_php_error(new \ErrorException($last_error['message'], $last_error['type'], 0, $last_error['file'], $last_error['line']));

			exit(1);
		}
	}

	public static function exception_handler(\Exception $e)
	{
		$severity = ( ! isset(static::$levels[$e->getCode()])) ? $e->getCode() : static::$levels[$e->getCode()];
		Log::error($severity.' - '.$e->getMessage().' in '.$e->getFile().' on line '.$e->getLine());

		static::show_php_error($e);
	}

	public static function error_handler($severity, $message, $filepath, $line)
	{
		Log::error($severity.' - '.$message.' in '.$filepath.' on line '.$line);

		if (($severity & error_reporting()) == $severity)
		{
			static::show_php_error(new \ErrorException($message, $severity, 0, $filepath, $line));
		}
		return true;
	}

	public static function show_php_error(\Exception $e)
	{
		static::$count++;
		$data['type']		= get_class($e);
		$data['severity']	= $e->getCode();
		$data['message']	= $e->getMessage();
		$data['filepath']	= $e->getFile();
		$data['error_line']	= $e->getLine();
		$data['backtrace']	= $e->getTrace();

		array_shift($data['backtrace']);
		
		foreach ($data['backtrace'] as $key => $trace)
		{
			if ( ! isset($trace['file']))
			{
				unset($data['backtrace'][$key]);
			}
			elseif (strncmp($trace['file'], APPPATH, strlen(APPPATH)) !== 0)
			{
				unset($data['backtrace'][$key]);
			}
		}

		$data['severity'] = ( ! isset(static::$levels[$data['severity']])) ? $data['severity'] : static::$levels[$data['severity']];

		$data['debug_lines'] = Debug::file_lines($data['filepath'], $data['error_line']);

		$data['filepath'] = Fuel::clean_path($data['filepath']);

		$data['filepath'] = str_replace("\\", "/", $data['filepath']);

		echo View::factory('errors'.DS.'php_error', $data);
	}

	public static function notice($msg)
	{
		if ( ! in_array(Fuel::$env, array('test', 'dev')))
		{
			return;
		}

		$trace = Arr::element(debug_backtrace(), 1);

		$data['message']	= $msg;
		$data['filepath']	= str_replace("\\", "/", Fuel::clean_path($trace['file']));
		$data['line']		= $trace['line'];
		$data['function']	= $trace['function'];

		echo View::factory('errors'.DS.'php_notice', $data);
	}

}

/* End of file error.php */