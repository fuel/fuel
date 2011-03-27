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
	 * @var	object	Holds the previous request;
	 */
	protected static $previous = false;

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
	 * @param	bool	if true use routes to process the URI
	 * @return	object	The new request
	 */
	public static function factory($uri = null, $route = true)
	{
		logger(Fuel::L_INFO, 'Creating a new Request with URI = "'.$uri.'"', __METHOD__);

		if (static::$active)
		{
			static::$previous = static::$active;
		}

		static::$active = new static($uri, $route);

		if ( ! static::$main)
		{
			logger(Fuel::L_INFO, 'Setting main Request', __METHOD__);
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
		logger(Fuel::L_INFO, 'Called', __METHOD__);

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
		class_exists('Log', false) && logger(Fuel::L_INFO, 'Called', __METHOD__);

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
	public static function show_404($return = false)
	{
		logger(Fuel::L_INFO, 'Called', __METHOD__);

		// This ensures that show_404 is only called once.
		static $call_count = 0;
		$call_count++;

		if ($call_count > 1)
		{
			throw new \Fuel_Exception('It appears your _404_ route is incorrect.  Multiple Recursion has happened.');
		}

		if (\Config::get('routes._404_') === null)
		{
			$response = new \Response(\View::factory('404'), 404);

			if ($return)
			{
				return $response;
			}

			$response->send(true);
			exit;
		}
		else
		{
			$request = \Request::factory(\Config::get('routes._404_'))->execute();

			if ($return)
			{
				return $request->response;
			}

			$request->response->send(true);
			exit;
		}
	}

	public static function reset_request()
	{
		// Let's make the previous Request active since we are don't executing this one.
		if (static::$previous)
		{
			static::$active = static::$previous;
		}
	}


	/**
	 * @var	string	Holds the response of the request.
	 */
	public $response = null;

	/**
	 * @var	object	The request's URI object
	 */
	public $uri = '';

	/**
	 * @var	object	The request's route object
	 */
	public $route = null;

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
	public $action = '';

	/**
	 * @var	string	The request's method params
	 */
	public $method_params = array();

	/**
	 * @var	string	The request's named params
	 */
	public $named_params = array();

	/**
	 * @var	Controller	Controller instance once instantiated
	 */
	public $controller_instance;

	/**
	 * @var	array	search paths for the current active request
	 */
	public $paths = array();

	/**
	 * Creates the new Request object by getting a new URI object, then parsing
	 * the uri with the Route class.
	 *
	 * @access	public
	 * @param	string	the uri string
	 * @param	bool	whether or not to route the URI
	 * @return	void
	 */
	public function __construct($uri, $route = true)
	{
		$this->uri = new \URI($uri);

		$this->route = \Router::process($this, $route);

		if ( ! $this->route)
		{
			return false;
		}

		if ($this->route->module !== null)
		{
			$this->module = $this->route->module;
			\Fuel::add_module($this->module);
			$this->add_path(\Fuel::module_exists($this->module));
		}

		$this->directory = $this->route->directory;
		$this->controller = $this->route->controller;
		$this->action = $this->route->action;
		$this->method_params = $this->route->method_params;
		$this->named_params = $this->route->named_params;
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
		logger(Fuel::L_INFO, 'Called', __METHOD__);

		if ( ! $this->route)
		{
			$this->response = static::show_404(true);
			static::reset_request();
			return $this;
		}

		$controller_prefix = '\\'.($this->module ? ucfirst($this->module).'\\' : '').'Controller_';
		$method_prefix = 'action_';

		$class = $controller_prefix.($this->directory ? ucfirst($this->directory).'_' : '').ucfirst($this->controller);
		$method = $this->action;

		// If the class doesn't exist then 404
		if ( ! class_exists($class))
		{
			$this->response = static::show_404(true);
			static::reset_request();
			return $this;
		}

		logger(Fuel::L_INFO, 'Loading controller '.$class, __METHOD__);
		$this->controller_instance = $controller = new $class($this, new \Response);

		$method = $method_prefix.($method ?: (property_exists($controller, 'default_action') ? $controller->default_action : 'index'));


		// Allow to do in controller routing if method router(action, params) exists
		if (method_exists($controller, 'router'))
		{
			$method = 'router';
			$this->method_params = array($this->action, $this->method_params);
		}

		if (is_callable(array($controller, $method)))
		{
			// Call the before method if it exists
			if (method_exists($controller, 'before'))
			{
				logger(Fuel::L_INFO, 'Calling '.$class.'::before', __METHOD__);
				$controller->before();
			}

			logger(Fuel::L_INFO, 'Calling '.$class.'::'.$method, __METHOD__);
			call_user_func_array(array($controller, $method), $this->method_params);

			// Call the after method if it exists
			if (method_exists($controller, 'after'))
			{
				logger(Fuel::L_INFO, 'Calling '.$class.'::after', __METHOD__);
				$controller->after();
			}

			// Get the controller's output
			$this->response =& $controller->response;
		}
		else
		{
			$this->response = static::show_404(true);
		}

		static::reset_request();
		return $this;
	}

	public function response()
	{
		return $this->response;
	}

	/**
	 * Add to paths which are used by Fuel::find_file()
	 *
	 * @param  string  the new path
	 * @param  bool    whether to add to the front or the back of the array
	 */
	public function add_path($path, $prefix = false)
	{
		if ($prefix)
		{
			// prefix the path to the paths array
			array_unshift($this->paths, $path);
		}
		else
		{
			// add the new path
			$this->paths[] = $path;
		}
	}

	/**
	 * Returns the array of currently loaded search paths.
	 *
	 * @return  array  the array of paths
	 */
	public function get_paths()
	{
		return $this->paths;
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
		return (string) $this->response;
	}
}

/* End of file request.php */
