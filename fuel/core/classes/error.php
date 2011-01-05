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
 * @copyright	2010 - 2011 Fuel Development Team
 * @link		http://fuelphp.com
 */

namespace Fuel\Core;



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
	 * @access	public
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
			logger(Fuel::L_ERROR, $severity.' - '.$last_error['message'].' in '.$last_error['file'].' on line '.$last_error['line']);

			if (\Fuel::$env != Fuel::PRODUCTION)
			{
				static::show_php_error(new \ErrorException($last_error['message'], $last_error['type'], 0, $last_error['file'], $last_error['line']));
			}
			else
			{
				echo 'An unrecoverable error occurred.';
			}

			exit(1);
		}
	}

	public static function exception_handler(\Exception $e)
	{
		$severity = ( ! isset(static::$levels[$e->getCode()])) ? $e->getCode() : static::$levels[$e->getCode()];
		logger(Fuel::L_ERROR, $severity.' - '.$e->getMessage().' in '.$e->getFile().' on line '.$e->getLine());

		if (\Fuel::$env != Fuel::PRODUCTION)
		{
			static::show_php_error($e);
		}
		else
		{
			echo 'An unrecoverable exception was thrown.';
		}
	}

	public static function error_handler($severity, $message, $filepath, $line)
	{
		if (static::$count <= Config::get('error_throttling', 10))
		{
			logger(Fuel::L_ERROR, $severity.' - '.$message.' in '.$filepath.' on line '.$line);

			if (\Fuel::$env != \Fuel::PRODUCTION && ($severity & error_reporting()) == $severity)
			{
				static::$count++;
				static::show_php_error(new \ErrorException($message, $severity, 0, $filepath, $line));
			}
		}
		elseif (\Fuel::$env != \Fuel::PRODUCTION
				&& static::$count == (\Config::get('error_throttling', 10) + 1)
				&& ($severity & error_reporting()) == $severity)
		{
			static::$count++;
			static::notice('Error throttling threshold was reached, no more full error reports are shown.', true);
		}

		return true;
	}

	public static function show_php_error(\Exception $e)
	{
		$data['type']		= get_class($e);
		$data['severity']	= $e->getCode();
		$data['message']	= $e->getMessage();
		$data['filepath']	= $e->getFile();
		$data['error_line']	= $e->getLine();
		$data['backtrace']	= $e->getTrace();

		$data['severity'] = ( ! isset(static::$levels[$data['severity']])) ? $data['severity'] : static::$levels[$data['severity']];

		if (\Fuel::$is_cli)
		{
			\Cli::write(\Cli::color($data['severity'].' - '.$data['message'].' in '.\Fuel::clean_path($data['filepath']).' on line '.$data['error_line'], 'red'));
			return;
		}

		$debug_lines = array();

		foreach ($data['backtrace'] as $key => $trace)
		{
			if ( ! isset($trace['file']))
			{
				unset($data['backtrace'][$key]);
			}
			elseif ($trace['file'] == COREPATH.'classes/error.php')
			{
				unset($data['backtrace'][$key]);
			}
		}

		$debug_lines = array(
			'file'	=> $data['filepath'],
			'line'	=> $data['error_line']
		);

		$data['severity'] = ( ! isset(static::$levels[$data['severity']])) ? $data['severity'] : static::$levels[$data['severity']];

		$data['debug_lines'] = \Debug::file_lines($debug_lines['file'], $debug_lines['line']);

		$data['filepath'] = \Fuel::clean_path($debug_lines['file']);

		$data['filepath'] = str_replace("\\", "/", $data['filepath']);
		$data['error_line'] = $debug_lines['line'];

		echo \View::factory('errors'.DS.'php_error', $data);
	}

	public static function notice($msg, $always_show = false)
	{
		if ( ! $always_show && (\Fuel::$env == \Fuel::PRODUCTION || \Config::get('show_notices', true) === false))
		{
			return;
		}

		$trace = array_merge(array('file' => '(unknown)', 'line' => '(unknown)'), \Arr::element(debug_backtrace(), 1));

		logger(Fuel::L_DEBUG, 'Notice - '.$msg.' in '.$trace['file'].' on line '.$trace['line']);

		$data['message']	= $msg;
		$data['type']		= 'Notice';
		$data['filepath']	= \Fuel::clean_path($trace['file']);
		$data['line']		= $trace['line'];
		$data['function']	= $trace['function'];

		echo \View::factory('errors'.DS.'php_short', $data);
	}

}

/* End of file error.php */
