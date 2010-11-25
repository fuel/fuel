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
		return isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
	}

	/**
	 * Return's the user agent
	 *
	 * @access	public
	 * @return	string
	 */
	public static function user_agent()
	{
		return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
	}

	/**
	 * Fetch an item from the GET array
	 *
	 * @access	public
	 * @param	string	The index key
	 * @param	mixed	The default value
	 * @return	string
	 */
	public static function get($index = '', $default = false)
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
	public static function post($index = '', $default = false)
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
	public static function put($index = '', $default = false)
	{
		if (static::method() !== 'PUT')
		{
			return NULL;
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
	public static function delete($index = '', $default = false)
	{
		if (static::method() !== 'DELETE')
		{
			return NULL;
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
	public static function get_post($index = '', $default = false)
	{
		return isset($_POST[$index]) ? static::post($index, $default) : static::get($index, $default);
	}

	/**
	 * Fetch an item from the COOKIE array
	 *
	 * @access	public
	 * @param	string	The index key
	 * @param	mixed	The default value
	 * @return	string
	 */
	function cookie($index = '', $default = false)
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
	function server($index = '', $default = false)
	{
		return static::_fetch_from_array($_SERVER, $index, $default);
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
	private function _fetch_from_array(&$array, $index = '', $default = false)
	{
		if ( ! isset($array[$index]))
		{
			return $default;
		}

		return $array[$index];
	}
	
}

/* End of file input.php */