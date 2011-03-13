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
 * Log Class
 *
 * @package		Fuel
 * @category	Logging
 * @author		Phil Sturgeon
 * @link		http://fuelphp.com/docs/classes/log.html
 */
class Log {

	public static function info($msg, $method = null)
	{
		return static::write(\Fuel::L_INFO, $msg, $method);
	}

	// --------------------------------------------------------------------

	public static function debug($msg, $method = null)
	{
		return static::write(\Fuel::L_DEBUG, $msg, $method);
	}

	// --------------------------------------------------------------------

	public static function error($msg, $method = null)
	{
		return static::write(\Fuel::L_ERROR, $msg, $method);
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
	public static function write($level, $msg, $method = null)
	{
		switch ($level)
		{
			case \Fuel::L_ERROR:
				$level = 'Error';
			break;
			case \Fuel::L_DEBUG:
				$level = 'Debug';
			break;
			case \Fuel::L_INFO:
				$level = 'Info';
			break;
		}

		$filepath = \Config::get('log_path').date('Y/m').'/';

		if ( ! is_dir($filepath))
		{
			$old = umask(0);
			mkdir($filepath, 0777, true);
			chmod($filepath, 0777);
			umask($old);
		}

		$filename = $filepath.date('d').'.php';

		$message  = '';

		if ( ! file_exists($filename))
		{
			$message .= "<"."?php defined('COREPATH') or exit('No direct script access allowed'); ?".">".PHP_EOL.PHP_EOL;
		}

		if ( ! $fp = @fopen($filename, 'a'))
		{
			return false;
		}

		$call = '';
		if ( ! empty($method))
		{
			$call .= $method;
		}

		$message .= $level.' '.(($level == 'info') ? ' -' : '-').' ';
		$message .= date(\Config::get('log_date_format'));
		$message .= ' --> '.(empty($call) ? '' : $call.' - ').$msg.PHP_EOL;

		flock($fp, LOCK_EX);
		fwrite($fp, $message);
		flock($fp, LOCK_UN);
		fclose($fp);

		$old = umask(0);
		@chmod($filename, 0666);
		umask($old);
		return true;
	}

}

/* End of file log.php */
