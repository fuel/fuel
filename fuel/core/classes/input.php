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
 * Input class
 *
 * The input class allows you to access HTTP parameters, load server variables
 * and user agent details.
 *
 * @package		Fuel
 * @category	Core
 * @author		Phil Sturgeon
 * @link		http://fuelphp.com/docs/classes/input.html
 */
class Input {

	/**
	 * Get the real ip address of the user.  Even if they are using a proxy.
	 *
	 * @static
	 * @access	public
	 * @return	string
	 */
	public static function real_ip()
	{
		if (static::server('HTTP_X_FORWARDED_FOR') !== null)
		{
			return static::server('HTTP_X_FORWARDED_FOR');
		}
		elseif (static::server('HTTP_CLIENT_IP') !== null)
		{
			return static::server('HTTP_CLIENT_IP');
		}
		elseif (static::server('REMOTE_ADDR') !== null)
		{
			return static::server('REMOTE_ADDR');
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
		return (static::server('HTTPS') !== null && static::server('HTTPS') != 'off') ? 'https' : 'http';
	}

	/**
	 * Return's whether this is an AJAX request or not
	 *
	 * @access	public
	 * @return	bool
	 */
	public static function is_ajax()
	{
		return (static::server('HTTP_X_REQUESTED_WITH') !== null) && strtolower(static::server('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest';
	}

	/**
	 * Return's the referrer
	 *
	 * @access	public
	 * @return	string
	 */
	public static function referrer()
	{
		return static::server('HTTP_REFERER', '');
	}

	/**
	 * Return's the input method used (GET, POST, DELETE, etc.)
	 *
	 * @access	public
	 * @return	string
	 */
	public static function method()
	{
		return static::server('REQUEST_METHOD', 'GET');
	}

	/**
	 * Return's the user agent
	 *
	 * @access	public
	 * @return	string
	 */
	public static function user_agent()
	{
		return static::server('HTTP_USER_AGENT', '');
	}

	/**
	 * Fetch an item from the GET array
	 *
	 * @access	public
	 * @param	string	The index key
	 * @param	mixed	The default value
	 * @return	string
	 */
	public static function get($index, $default = null)
	{
		return static::_fetch_from_array($_GET, $index, $default);
	}

	/**
	 * Fetch an item from the POST array
	 *
	 * @access	public
	 * @param	string	The index key
	 * @param	mixed	The default value
	 * @return	string
	 */
	public static function post($index, $default = null)
	{
		return static::_fetch_from_array($_POST, $index, $default);
	}

	/**
	 * Fetch an item from the php://input for put arguments
	 *
	 * @access	public
	 * @param	string	The index key
	 * @param	mixed	The default value
	 * @return	string
	 */
	public static function put($index, $default = null)
	{
		if (static::method() !== 'PUT')
		{
			return null;
		}

		if ( ! isset($_PUT))
		{
			static $_PUT;
			parse_str(file_get_contents('php://input'), $_PUT);
		}

		return static::_fetch_from_array($_PUT, $index, $default);
	}

	/**
	 * Fetch an item from the php://input for delete arguments
	 *
	 * @access	public
	 * @param	string	The index key
	 * @param	mixed	The default value
	 * @return	string
	 */
	public static function delete($index, $default = null)
	{
		if (static::method() !== 'DELETE')
		{
			return null;
		}

		if ( ! isset($_DELETE))
		{
			static $_DELETE;
			parse_str(file_get_contents('php://input'), $_DELETE);
		}

		return static::_fetch_from_array($_DELETE, $index, $default);
	}

	/**
	 * Fetch an item from either the GET array or the POST
	 *
	 * @access	public
	 * @param	string	The index key
	 * @param	mixed	The default value
	 * @return	string
	 */
	public static function get_post($index, $default = null)
	{
		return static::post($index, 's)meR4nD0ms+rIng') === 's)meR4nD0ms+rIng'
				? static::get($index, $default)
				: static::post($index, $default);
	}

	/**
	 * Fetch an item from the COOKIE array
	 *
	 * @access	public
	 * @param	string	The index key
	 * @param	mixed	The default value
	 * @return	string
	 */
	public static function cookie($index, $default = null)
	{
		return static::_fetch_from_array($_COOKIE, $index, $default);
	}

	/**
	 * Fetch an item from the SERVER array
	 *
	 * @access	public
	 * @param	string	The index key
	 * @param	mixed	The default value
	 * @return	string
	 */
	public static function server($index, $default = null)
	{
		return static::_fetch_from_array($_SERVER, strtoupper($index), $default);
	}

	/**
	 * Retrieve values from global arrays
	 *
	 * @access	private
	 * @param	array	The array
	 * @param	string	The index key
	 * @param	mixed	The default value
	 * @return	string
	 */
	private static function _fetch_from_array(&$array, $index, $default = null)
	{
		if (is_null($index))
		{
			return $array;
		}
		elseif ( ! isset($array[$index]))
		{
			return $default;
		}

		return $array[$index];
	}

}

/* End of file input.php */
