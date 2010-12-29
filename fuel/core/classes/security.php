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

namespace Fuel\Core;

use Fuel\App as App;

class Security {

	protected static $csrf_token_key = false;
	protected static $csrf_token = false;
	protected static $csrf_new_token = false;

	/**
	 * Fetches CSRF settings and current token
	 */
	public static function _init()
	{
		static::$csrf_token_key = App\Config::get('security.csrf_token_key', 'fuel_csrf_token');

		if (App\Config::get('security.csrf_autoload', false))
		{
			static::fetch_token();
		}
	}

	/**
	 * Doesn't do anything yet, just here because it will be and facilitates autoload ;-)
	 */
	public static function clean_input()
	{
		$filters = App\Config::get('security.input_filter');
		foreach ($filters as $filter)
		{
			if (is_callable('static::'.$filter))
			{
				$_GET = static::$filter($_GET);
				$_POST = static::$filter($_POST);
			}
			elseif (function_exists($filter))
			{
				foreach($_GET as $key => $value)
				{
					$_GET[$key] = $filter($value);
				}
				foreach($_POST as $key => $value)
				{
					$_POST[$key] = $filter($value);
				}
			}
		}
	}

	public static function strip_tags($value)
	{
		if ( ! is_array($value))
		{
			$value = filter_var($value, FILTER_SANITIZE_STRING);
		}
		else
		{
			foreach ($value as $k => $v)
			{
				$value[$k] = static::strip_tags($v);
			}
		}

		return $value;
	}

	/**
	 * Check CSRF Token
	 *
	 * @param	string	CSRF token to be checked, checks post when empty
	 * @return	bool
	 */
	public static function check_token($value = null)
	{
		$value = $value ?: App\Input::post(static::$csrf_token_key, 'fail');

		// always reset token once it's been checked
		static::regenerate_token();

		return $value === static::fetch_token();
	}

	/**
	 * Fetch CSRF Token from cookie
	 *
	 * @return	string
	 */
	public static function fetch_token()
	{
		if (static::$csrf_token !== false)
		{
			return static::$csrf_token;
		}

		static::$csrf_token = App\Input::cookie(static::$csrf_token_key, null);
		if (static::$csrf_token === null || App\Config::get('security.csrf_expiration', 0) <= 0)
		{
			// set new token for next session when necessary
			static::regenerate_token();
		}

		return static::$csrf_token;
	}

	/**
	 * Regenerate token
	 *
	 * Generates a new token if the old one expired or was checked.
	 */
	public static function regenerate_token()
	{
		if (static::$csrf_new_token !== false)
		{
			return;
		}

		static::$csrf_new_token = md5(uniqid().time());

		$expiration = App\Config::get('security.csrf_expiration', 0);
		App\Cookie::set(static::$csrf_token_key, static::$csrf_new_token, $expiration);
	}

	/**
	 * JS fetch token
	 *
	 * Produces JavaScript fuel_csrf_token() function that will return the current
	 * CSRF token when called. Use to fill right field on form submit for AJAX operations.
	 *
	 * @return string
	 */
	public static function js_fetch_token()
	{
		$output  = '<script type="text/javascript">
	function fuel_csrf_token()
	{
		if (document.cookie.length > 0)
		{
			var c_name = "'.static::$csrf_token_key.'";
			c_start = document.cookie.indexOf(c_name + "=");
			if (c_start != -1)
			{
				c_start = c_start + c_name.length + 1;
				c_end = document.cookie.indexOf(";" , c_start);
				if (c_end == -1)
				{
					c_end=document.cookie.length;
				}
				return unescape(document.cookie.substring(c_start, c_end));
			}
		}
		return "";
	}'.PHP_EOL;
		$output .= '</script>'.PHP_EOL;

		return $output;
	}
}

/* End of file security.php */
