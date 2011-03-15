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
 * Cookie class
 *
 * @package    Kohana
 * @category   Helpers
 * @author     Kohana Team
 * @modified   Phil Sturgeon - Fuel Development Team
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaframework.org/license
 * @link		http://fuelphp.com/docs/classes/cookie.html
 */
class Cookie {

	/**
	 * @var  integer  Number of seconds before the cookie expires
	 */
	public static $expiration = 0;

	/**
	 * @var  string  Restrict the path that the cookie is available to
	 */
	public static $path = '/';

	/**
	 * @var  string  Restrict the domain that the cookie is available to
	 */
	public static $domain = null;

	/**
	 * @var  boolean  Only transmit cookies over secure connections
	 */
	public static $secure = false;

	/**
	 * @var  boolean  Only transmit cookies over HTTP, disabling Javascript access
	 */
	public static $http_only = false;

	/**
	 * Gets the value of a signed cookie. Cookies without signatures will not
	 * be returned. If the cookie signature is present, but invalid, the cookie
	 * will be deleted.
	 *
	 *     // Get the "theme" cookie, or use "blue" if the cookie does not exist
	 *     $theme = Cookie::get('theme', 'blue');
	 *
	 * @param   string  cookie name
	 * @param   mixed   default value to return
	 * @return  string
	 */
	public static function get($name, $default = null)
	{
		return \Input::cookie($name, $default);
	}

	/**
	 * Sets a signed cookie. Note that all cookie values must be strings and no
	 * automatic serialization will be performed!
	 *
	 *     // Set the "theme" cookie
	 *     Cookie::set('theme', 'red');
	 *
	 * @param   string   name of cookie
	 * @param   string   value of cookie
	 * @param   integer  lifetime in seconds
	 * @param   string   path of the cookie
	 * @param   string   domain of the cookie
	 * @return  boolean
	 */
	public static function set($name, $value, $expiration = null, $path = null, $domain = null)
	{
		// If nothing is provided, use the standard amount of time
		if ($expiration === null)
		{
			$expiration = time() + 86500;
		}
		// If it's set, add the current time so we have an offset
		else
		{
			$expiration = $expiration > 0 ? $expiration + time() : 0;
		}

		// use the class defaults for path and domain if not provided
		if (empty($path))
		{
			$path = static::$path;
		}

		if (empty($domain))
		{
			$domain = static::$domain;
		}

		return setcookie($name, $value, $expiration, $path, $domain, static::$secure, static::$http_only);
	}

	/**
	 * Deletes a cookie by making the value null and expiring it.
	 *
	 *     Cookie::delete('theme');
	 *
	 * @param   string   cookie name
	 * @return  boolean
	 * @uses    static::set
	 */
	public static function delete($name)
	{
		// Remove the cookie
		unset($_COOKIE[$name]);

		// Nullify the cookie and make it expire
		return static::set($name, null, -86400);
	}
}

/* End of file cookie.php */
