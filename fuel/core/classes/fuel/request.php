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

class Request {

	/**
	 * @var	object	Holds the main request instance
	 */
	protected static $main = false;

	/**
	 * @var	object	Holds the global request instance
	 */
	protected static $active = false;

	/**
	 * Generates a new request.  The request is then set to be the active
	 * request.  If this is the first request, then save that as the main
	 * request for the app.
	 *
	 * Usage:
	 *
	 * <code>Request::factory('hello/world');</code>
	 *
	 * @access	public
	 * @param	string	The URI of the request
	 * @return	object	The new request
	 */
	public static function factory($uri = null)
	{
		Log::info('Creating a new Request with URI = "'.$uri.'"', __METHOD__);

		static::$active = new Request($uri);

		if ( ! static::$main)
		{
			Log::info('Setting main Request', __METHOD__);
			static::$main = static::$active;
		}

		return static::$active;
	}

	/**
	 * Returns the main request instance.
	 *
	 * Usage:
	 *
	 * <code>Request::main();</code>
	 *
	 * @access	public
	 * @return	object
	 */
	public static function main()
	{
		Log::info('Called', __METHOD__);

		return static::$main;
	}

	/**
	 * Returns the active request currently being used.
	 *
	 * Usage:
	 *
	 * <code>Request::active();</code>
	 *
	 * @access	public
	 * @return	object
	 */
	public static function active()
	{
		Log::info('Called', __METHOD__);

		return static::$active;
	}

	/**
	 * Shows a 404.  Checks to see if a 404_override route is set, if not show
	 * a default 404.
	 *
	 * Usage:
	 *
	 * <code>Request::show_404();</code>
	 *
	 * @access	public
	 * @return	void
	 */
	public static function show_404()
	{
		Log::info('Called', __METHOD__);

		if (Config::get('routes.404') === false)
		{
			static::active()->output = View::factory('404');
		}
		else
		{
			list($controller, $action) = array_pad(explode('/', Config::get('routes.404')), 2, false);

			$action or $action = 'index';

			$class = APP_NAMESPACE.'\\Controller_'.ucfirst($controller);
			$method = 'action_'.$action;

			if (class_exists($class))
			{
				$controller = new $class(static::active());
				if (method_exists($controller, $method))
				{
					// Call the before method if it exists
					if (method_exists($controller, 'before'))
					{
						$controller->before();
					}

					$controller->{$method}();

					// Call the after method if it exists
					if (method_exists($controller, 'after'))
					{
						$controller->after();
					}

					// Get the controller's output
					static::active()->output =& $controller->output;
				}
				else
				{
					throw new Exception('404 Action not found.');
				}
			}
			else
			{
				throw new Exception('404 Controller not found.');
			}
		}
	}

	/**
	 * @var	string	Holds the response of the request.
	 */
	public $output = NULL;

	/**
	 * @var	object	The request's URI object
	 */
	public $uri = '';

	/**
	 * @var	string	Controller module
	 */
	public $module = '';

	/**
	 * @var	string	Controller directory
	 */
	public $directory = '';

	/**
	 * @var	string	The request's controller
	 */
	public $controller = '';

	/**
	 * @var	string	The request's action
	 */
	public $action = 'index';

	/**
	 * @var	string	The request's method params
	 */
	public $method_params = array();

	/**
	 * @var	string	The request's named params
	 */
	public $named_params = array();

	/**
	 * Creates the new Request object by getting a new URI object, then parsing
	 * the uri with the Route class.
	 *
	 * @access	public
	 * @param	string	the uri string
	 * @return	void
	 */
	public function __construct($uri)
	{
		$this->uri = new URI($uri);
		$route = Route::parse($this->uri);

		// Check for module
		$module = Fuel::$packages['app']->prefix_path(ucfirst($route['uri_array'][0]).'_');
		if ($module === false)
		{
			foreach (Config::get('module_paths', array()) as $path)
			{
				if (is_dir($mod_path = $path.strtolower($route['uri_array'][0].DS)))
				{
					// Load module and end search
					Fuel::$packages['app']->add_prefix(ucfirst($route['uri_array'][0]).'_', $mod_path);
					$this->module = array_shift($route['uri_array']);
					break;
				}
			}
		}

		// Check for directory
		if ($route['uri_array'][0] != 'index')
		{
			$path = ( ! empty($this->module) ? $mod_path : APPPATH).'classes'.DS.'controller'.DS;
			if (is_dir($dirpath = $path.strtolower($route['uri_array'][0])))
			{
				$this->directory = $route['uri_array'][0];
				array_shift($route['uri_array']);
			}
		}

		// When emptied the controller defaults to module or dirname, action still defaults to index
		$controller = empty($this->directory) ? $this->module : $this->directory;
		if (count($route['uri_array']) == 0)
		{
			$route['uri_array'] = array($controller, 'index');
		}
		elseif ($route['uri_array'][0] == 'index')
		{
			array_unshift($route['uri_array'], $controller);
		}
		elseif (count($route['uri_array']) == 1)
		{
			$route['uri_array'][] = 'index';
		}

		$this->controller = $route['uri_array'][0];
		$this->action = $route['uri_array'][1];
		$this->method_params = array_slice($route['uri_array'], 2);
		$this->named_params = $route['named_params'];
		unset($route);
	}

	/**
	 * This executes the request and sets the output to be used later.
	 *
	 * Usage:
	 *
	 * <code>$request = Request::factory('hello/world')->execute();</code>
	 *
	 * @access	public
	 * @return	void
	 */
	public function execute()
	{
		Log::info('Called', __METHOD__);

		$controller_prefix = APP_NAMESPACE.'\\Controller_';
		$class = $controller_prefix.(empty($this->directory) ? '' : $this->directory.'_').ucfirst($this->controller);
		$method = 'action_'.$this->action;

		// if it's in a subdirectory or module, allow omitting the controller name if it's the same
		if ( ! class_exists($class))
		{
			// set the new controller to directory or module when applicable
			$controller = empty($this->directory) ? $this->module : $this->directory;
			// ... or to the default controller if neither was
			$controller = empty($controller) ? array_shift(explode('/', Route::$routes['default'])) : $controller;

			// try again with new controller if it changed
			if ($controller != $this->controller)
			{
				$class = $controller_prefix.(empty($this->directory) ? '' : $this->directory.'_').ucfirst($controller);
				if ($this->action != 'index')
				{
					array_unshift($this->method_params, $this->action);
				}
				$this->action = $this->controller;
				$method = 'action_'.$this->action;
				$this->controller = $controller;

				// autoload
				class_exists($class);
			}

			// 404 if it's still not found
			if ( ! class_exists($class, false))
			{
				static::show_404();
				return $this;
			}
		}

		Log::info('Loading controller '.$class, __METHOD__);
		$controller = new $class($this);
		foreach(array($method, 'action_404') as $action)
		{
			if (method_exists($controller, $action))
			{
				// Call the before method if it exists
				if (method_exists($controller, 'before'))
				{
					Log::info('Calling '.$class.'::before', __METHOD__);
					$controller->before();
				}

				Log::info('Calling '.$class.'::'.$action, __METHOD__);
				call_user_func_array(array($controller, $action), $this->method_params);

				// Call the after method if it exists
				if (method_exists($controller, 'after'))
				{
					Log::info('Calling '.$class.'::after', __METHOD__);
					$controller->after();
				}

				// Get the controller's output
				$this->output =& $controller->output;

				return $this;
			}
		}
		static::show_404();

		return $this;
	}

	/**
	 * PHP magic function returns the Output of the request.
	 *
	 * Usage:
	 *
	 * <code>
	 * $request = Request::factory('hello/world')->execute();
	 * echo $request;
	 * </code>
	 *
	 * @access	public
	 * @return	string
	 */
	public function __toString()
	{
		return $this->output;
	}
}

/* End of file request.php */