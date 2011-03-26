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

		$name = $path;
		if (is_array($options) and array_key_exists('name', $options))
		{
			$name = $options['name'];
			unset($options['name']);
			if (count($options) == 1 and ! is_array($options[0]))
			{
				$options = $options[0];
			}
		}

		static::$routes[$name] = new \Route($path, $options);
	}

	/**
	 * Does reverse routing for a named route.  This will return the FULL url
	 * (including the base url and index.php).
	 *
	 * WARNING: This is VERY limited at this point.  Does not work if there is
	 * any regex in the route.
	 *
	 * Usage:
	 *
	 * <a href="<?php echo Router::get('foo'); ?>">Foo</a>
	 *
	 * @param   string  $name  the name of the route
	 * @param   array   $named_params  the array of named parameters
	 * @return  string  the full url for the named route
	 */
	public static function get($name, $named_params = array())
	{
		if (array_key_exists($name, static::$routes))
		{
			return \Uri::create(static::$routes[$name]->path, $named_params);
		}
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

		return  static::find_controller($match);
	}

	/**
	 * Find the controller that matches the route requested
	 *
	 * @param	Route		the given Route object
	 * @return	mixed		the match array or false
	 */
	protected static function find_controller($match)
	{
		// First port of call: request for a module?
		if (\Fuel::module_exists($match->segments[0]))
		{
			// make the module known to the autoloader
			\Fuel::add_module($match->segments[0]);

			$segments = $match->segments;

			// first check if the controller is in a directory.
			$match->module = array_shift($segments);
			$match->directory = count($segments) ? array_shift($segments) : null;
			$match->controller = count($segments) ? array_shift($segments) : $match->module;

			// does the module controller exist?
			if (class_exists(ucfirst($match->module).'\\Controller_'.ucfirst($match->directory).'_'.ucfirst($match->controller)))
			{
				$match->action = count($segments) ? array_shift($segments) : 'index';
				$match->method_params = $segments;
				return $match;
			}

			$segments = $match->segments;

			// then check if it's a module controller
			$match->module = array_shift($segments);
			$match->directory = null;
			$match->controller = count($segments) ? array_shift($segments) : $match->module;

			// does the module controller exist?
			if (class_exists(ucfirst($match->module).'\\Controller_'.ucfirst($match->controller)))
			{
				$match->action = count($segments) ? array_shift($segments) : 'index';
				$match->method_params = $segments;
				return $match;
			}
		}

		$segments = $match->segments;

		// It's not a module, first check if the controller is in a directory.
		$match->directory = array_shift($segments);
		$match->controller = count($segments) ? array_shift($segments) : $match->directory;

		if (class_exists('Controller_'.ucfirst($match->directory).'_'.ucfirst($match->controller)))
		{
			$match->action = count($segments) ? array_shift($segments) : 'index';
			$match->method_params = $segments;
			return $match;
		}

		$segments = $match->segments;

		// It's not in a directory, so check for app controllers
		$match->directory = null;
		$match->controller = count($segments) ? array_shift($segments) : $match->directory;

		// We first want to check if the controller is in a directory.
		if (class_exists('Controller_'.ucfirst($match->controller)))
		{
			$match->action = count($segments) ? array_shift($segments) : 'index';
			$match->method_params = $segments;
			return $match;
		}

		// none of the above. I give up...
		return false;
	}
}

/* End of file router.php */
