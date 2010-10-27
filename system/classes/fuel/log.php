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

// --------------------------------------------------------------------

/**
 * Log Class
 *
 * @package		Fuel
 * @category	Logging
 * @author		Phil Sturgeon
 */

class Fuel_Log
{
	protected static $_levels = array('error' => 1, 'debug' => 2,  'info' => 3, 'all' => 4);

	public static function info($msg)
	{
		return Log::_write('info', $msg);
	}

	// --------------------------------------------------------------------

	public static function debug($msg)
	{
		return Log::_write('debug', $msg);
	}

	// --------------------------------------------------------------------

	public static function error($msg)
	{
		return Log::_write('error', $msg);
	}

	// --------------------------------------------------------------------

	/**
	 * Write Log File
	 *
	 * Generally this function will be called using the global log_message() function
	 *
	 * @access	public
	 * @param	string	the error level
	 * @param	string	the error message
	 * @return	bool
	 */
	private static function _write($level, $msg)
	{
		if ( ! isset(self::$_levels[$level]) OR (self::$_levels[$level] > Config::get('log_threshold')))
		{
			return FALSE;
		}

		$filepath = Config::get('log_path').date('Y/m').'/';

		if ( ! is_dir($filepath))
		{
			mkdir($filepath, 0777, TRUE);
			chmod($filepath, 0777);
		}

		$filename = $filepath.date('d').EXT;
		
		$message  = '';

		if ( ! file_exists($filename))
		{
			$message .= "<"."?php defined('SYSPATH') OR exit('No direct script access allowed'); ?".">".PHP_EOL.PHP_EOL;
		}

		if ( ! $fp = @fopen($filename, 'a'))
		{
			return FALSE;
		}

		$message .= $level.' '.(($level == 'info') ? ' -' : '-').' '.date(Config::get('log_date_format')). ' --> '.$msg.PHP_EOL;

		flock($fp, LOCK_EX);
		fwrite($fp, $message);
		flock($fp, LOCK_UN);
		fclose($fp);

		@chmod($filename, 0666);
		return TRUE;
	}

}

/* End of file log.php */