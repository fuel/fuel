<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Carbon
 *
 * Carbon is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Carbon
 * @version		1.0
 * @author		Dan Horrigan <http://dhorrigan.com>
 * @license		Apache License v2.0
 * @copyright	2010 Dan Horrigan
 */

class Carbon_Input {
	/**
	 * Get the real ip address of the user.  Even if they are using a proxy.
	 *
	 * @static
	 * @access	public
	 * @return	string
	 */
	public static function real_ip()
	{
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		elseif (isset($_SERVER['HTTP_CLIENT_IP']))
		{
			return $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (isset($_SERVER['REMOTE_ADDR']))
		{
			return $_SERVER['REMOTE_ADDR'];
		}
	}

	/**
	 * Return's the protocol that the request was made with
	 *
	 * @access	public
	 * @return	string
	 */
	public static function protocol()
	{
		return ( ! empty($_SERVER['HTTPS'])) ? 'https' : 'http';
	}

	/**
	 * Return's whether this is an AJAX request or not
	 *
	 * @access	public
	 * @return	bool
	 */
	public static function is_ajax()
	{
		return ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'));
	}

	/**
	 * Return's the referrer
	 *
	 * @access	public
	 * @return	string
	 */
	public static function referrer()
	{
		return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
	}

	/**
	 * Return's the input method used (GET, POST, DELETE, etc.)
	 *
	 * @access	public
	 * @return	string
	 */
	public static function method()
	{
		return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
	}

	/**
	 * Return's the user agent
	 *
	 * @access	public
	 * @return	string
	 */
	public static function user_agent()
	{
		return isset($_SERVER['HTTP_USER_AGENT']) ? isset($_SERVER['HTTP_USER_AGENT']) : '';
	}
}

/* End of file input.php */