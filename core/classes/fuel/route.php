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
			if ( ! isset(Route::$routes['default']) || Route::$routes['default'] == '')
			{
				// TODO: write logic to deal with missing default route.
				return FALSE;
			}
			else
			{
				return Route::parse_match(Route::$routes['default']);
			}
		}

		foreach (Route::$routes as $search => $route)
		{
			$search = str_replace(array(':any', ':segment'), array('.+', '/.+'), $search);
			if (preg_match('#'.$search.'#uD', $uri->uri) != false)
			{
				// TODO: Write the advanced routing.
				$route = preg_replace('#'.$search.'#uD', $route, $uri->uri);
				return Route::parse_match($route);
			}
		}
		
		return Route::parse_match($uri->uri);
	}
	
	/**
	 * Parses a route match and returns the controller, action and params.
	 *
	 * @access	protected
	 * @param	string	The matched route
	 * @return	array
	 */
	protected static function parse_match($route)
	{
		if (is_array($route))
		{
			// TODO: Write the advanced routing.
			if ( ! isset($route['params']))
			{
				$routes['params'] = array();
			}
			return $route;
		}
		else
		{
			list($controller, $action) = array_pad(explode('/', $route), 2, 'index');
			return array(
				'uri'			=> $route,
				'controller'	=> $controller,
				'action'		=> $action,
				'params'		=> array(),
			);
		}
	}
	
}

/* End of file route.php */