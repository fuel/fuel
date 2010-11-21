<?php defined('COREPATH') or die('No direct script access.');
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

class Fuel_Route {
	
	public static $routes = array();

	/**
	 * Attemptes to find the correct route for the given URI
	 *
	 * @access	public
	 * @param	object	The URI object
	 * @return	array
	 */
	public static function parse($uri)
	{
		// This handles the default route
		if ($uri->uri == '')
		{
			if ( ! isset(static::$routes['default']) || static::$routes['default'] == '')
			{
				// TODO: write logic to deal with missing default route.
				return FALSE;
			}
			else
			{
				return static::parse_match(static::$routes['default']);
			}
		}

		foreach (static::$routes as $search => $route)
		{
			$search = str_replace(array(':any', ':segment'), array('.+', '[^/]+'), $search);
			$search = preg_replace('#:([a-z]+)#uD', '(?P<$1>.+)', $search);

			if (preg_match('#'.$search.'#uD', $uri->uri, $params) != false)
			{
				$route = preg_replace('#'.$search.'#uD', $route, $uri->uri);

				return static::parse_match($route, $params);
			}
		}
		
		return static::parse_match($uri->uri);
	}
	
	/**
	 * Parses a route match and returns the controller, action and params.
	 *
	 * @access	protected
	 * @param	string	The matched route
	 * @return	array
	 */
	protected static function parse_match($route, $named_params = array())
	{
		$method_params = array();

		$segments = array_pad(explode('/', $route), 2, 'index');
		
		if (count($segments) > 2)
		{
			$method_params = array_slice($segments, 2);
		}

		// Clean out all the non-named stuff out of $named_params
		foreach($named_params as $key => $val)
		{
			if (is_numeric($key))
			{
				unset($named_params[$key]);
			}
		}

		return array(
			'uri'			=> $route,
			'controller'	=> $segments[0],
			'action'		=> $segments[1],
			'method_params'	=> $method_params,
			'named_params'	=> $named_params,
		);
	}
	
}

/* End of file route.php */