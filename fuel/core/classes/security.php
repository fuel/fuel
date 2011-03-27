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
 * Security Class
 *
 * @package		Fuel
 * @category	Core
 * @author		Dan Horrigan
 * @link		http://fuelphp.com/docs/classes/security.html
 */
class Security {

	protected static $csrf_token_key = false;
	protected static $csrf_token = false;
	protected static $csrf_new_token = false;

	/**
	 * Fetches CSRF settings and current token
	 */
	public static function _init()
	{
		static::$csrf_token_key = \Config::get('security.csrf_token_key', 'fuel_csrf_token');

		if (\Config::get('security.csrf_autoload', false))
		{
			static::fetch_token();
		}
	}

	/**
	 * Cleans the request URI
	 */
	public static function clean_uri($uri)
	{
		$filters = \Config::get('security.uri_filter', array());
		$filters = is_array($filters) ? $filters : array($filters);

		return static::clean($uri, $filters);
	}

	/**
	 * Cleans the global $_GET, $_POST and $_COOKIE arrays
	 */
	public static function clean_input()
	{
		$_GET		= static::clean($_GET);
		$_POST		= static::clean($_POST);
		$_COOKIE	= static::clean($_COOKIE);
	}

	/**
	 * Generic variable clean method
	 */
	public static function clean($var, $filters = null)
	{
		is_null($filters) && $filters = \Config::get('security.input_filter', array());
		$filters = is_array($filters) ? $filters : array($filters);

		foreach ($filters as $filter)
		{
			// is this filter a callable local function?
			if (is_string($filter) and is_callable('static::'.$filter))
			{
				$var = static::$filter($var);
			}

			// is this filter a callable function?
			elseif (is_callable($filter))
			{
				if (is_array($var))
				{
					foreach($var as $key => $value)
					{
						$var[$key] = call_user_func($filter, $value);
					}
				}
				else
				{
					$var = call_user_func($filter, $var);
				}
			}

			// assume it's a regex of characters to filter
			else
			{
				if (is_array($var))
				{
					foreach($var as $key => $value)
					{
						$var[$key] = preg_replace('#['.$filter.']#ui', '', $value);
					}
				}
				else
				{
					$var = preg_replace('#['.$filter.']#ui', '', $var);
				}
			}
		}
		return $var;
	}

	public static function xss_clean($value)
	{
		if ( ! is_array($value))
		{
			if ( ! function_exists('htmLawed'))
			{
				import('htmlawed/htmlawed', 'vendor');
			}

			return htmLawed($value, array('safe' => 1, 'balanced' => 0));
		}

		foreach ($value as $k => $v)
		{
			$value[$k] = static::xss_clean($v);
		}

		return $value;
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

	public static function htmlentities($value)
	{
		if (is_string($value))
		{
			$value = htmlentities($value, ENT_COMPAT, \Fuel::$encoding, false);
		}
		elseif (is_array($value) || $value instanceof \Iterator)
		{
			foreach ($value as $k => $v)
			{
				$value[$k] = static::htmlentities($v);
			}
		}
		elseif (is_object($value))
		{
			// Check if the object is whitelisted and return when that's the case
			foreach (\Config::get('security.whitelisted_classes') as $class)
			{
				if (is_a($value, $class))
				{
					return $value;
				}
			}

			// Throw exception when it wasn't whitelisted and can't be converted to String
			if ( ! method_exists($value, '__toString'))
			{
				throw new \Fuel_Exception('Object class was not whitelisted in security.whitelisted_classes and could '.
					'not be converted to string.');
			}

			$value = static::htmlentities((string) $value);
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
		$value = $value ?: \Input::post(static::$csrf_token_key, 'fail');

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

		static::$csrf_token = \Input::cookie(static::$csrf_token_key, null);
		if (static::$csrf_token === null || \Config::get('security.csrf_expiration', 0) <= 0)
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

		$expiration = \Config::get('security.csrf_expiration', 0);
		\Cookie::set(static::$csrf_token_key, static::$csrf_new_token, $expiration);
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
