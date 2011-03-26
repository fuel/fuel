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
 * @copyright	2010 - 2011 Fuel Development Team
 * @link		http://fuelphp.com
 */

namespace Fuel\Core;

class Router {

	public static $routes = array();

	public static function add($path, $options = null)
	{
		if (is_array($path))
		{
			foreach ($path as $p => $t)
			{
				static::add($p, $t);
			}
			return;
		}

		static::$routes[$path] = new \Route($path, $options);
	}

	/**
	 * Processes the given request using the defined routes
	 *
	 * @param	Request		the given Request object
	 * @param	bool		whether to use the defined routes or not
	 * @return	mixed		the match array or false
	 */
	public static function process(\Request $request, $route = true)
	{
		$match = false;

		if ($route)
		{
			foreach (static::$routes as $route)
			{
				if ($match = $route->parse($request))
				{
					break;
				}
			}
		}

		if ( ! $match)
		{
			// Since we didn't find a match, we will create a new route.
			$match = new Route($request->uri->get(), $request->uri->get());
			$match->parse($request);
		}

		$segments = $match->segments;

		if (static::find_controller($match, $segments))
		{
			// Check for a module
			if ($match->module !== null)
			{
				// search for a module controller
				if (count($segments) > 1)
				{
					// reset the found controller and action
					$match->controller = $match->action = null;
					array_shift($segments);
					static::find_controller($match, $segments);
				}
			}
			return $match;
		}

		return false;
	}

	protected static function find_controller( & $match, $segments)
	{
		// We first want to check if the controller is in a directory.  This way directories
		// can have the same name as a base controller and still work.
		if ($match->controller === null && count($segments) > 1)
		{
			if ($controller_path = \Fuel::find_file('classes'.DS.'controller'.DS.$segments[0], $segments[1]))
			{
				$match->directory = $segments[0];
				array_shift($segments);

				$match->controller = $segments[0];
				array_shift($segments);
			}
			elseif ($controller_path = \Fuel::find_file('classes'.DS.'controller'.DS.$segments[0], $segments[0]))
			{
				$match->directory = $segments[0];
				$match->controller = $segments[0];
				array_shift($segments);
			}
		}

		// Check for the controller
		if ($match->controller === null)
		{
			// Check for a module by this name
			if ($module_path = \Fuel::module_exists($segments[0]))
			{
				\Fuel::add_module($segments[0]);
			}
			if ($controller_path = \Fuel::find_file('classes'.DS.'controller', $segments[0]))
			{
				// did we find  a module controller?
				if (strpos($controller_path, APPPATH.'classes') !== 0 && $match->module === null)
				{
					$match->module = $segments[0];
				}
				$match->controller = $segments[0];
				array_shift($segments);
			}
		}

		if ($match->controller !== null)
		{
			// Since we found a controller lets see if there is an action defined
			if (count($segments) > 0)
			{
				$match->action = $segments[0];
				array_shift($segments);
			}

			$match->method_params = $segments;

			// We are all done here
			return true;
		}

		return false;
	}
}

/* End of file router.php */
