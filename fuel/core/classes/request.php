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
	 * @var	array	search paths for the current active request
	 */
	public $paths = array();

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
	public static function factory($uri = null)
	{
		logger(Fuel::L_INFO, 'Creating a new Request with URI = "'.$uri.'"', __METHOD__);

		static::$active = new static($uri);

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

		\Output::$status = 404;

		if ( ! isset(\Router::$routes['404']))
		{
			$output = \View::factory('404');

			if ($return)
			{
				return $output;
			}

			\Output::send_headers();
			exit($output);
		}
		else
		{
			list($controller, $action) = array_pad(explode('/', \Router::$routes['404']->translation), 2, false);

			$action or $action = 'index';

			$class = '\\Controller_'.ucfirst($controller);
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
					if ($return)
					{
						return $controller->output;
					}

					\Output::send_headers();
					exit($controller->output);
				}
				else
				{
					throw new \Fuel_Exception('404 Action not found.');
				}
			}
			else
			{
				throw new \Fuel_Exception('404 Controller not found.');
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
	 * Creates the new Request object by getting a new URI object, then parsing
	 * the uri with the Route class.
	 *
	 * @access	public
	 * @param	string	the uri string
	 * @param	bool	whether or not to route the URI
	 * @return	void
	 */
	public function __construct($uri)
	{
		$this->uri = new \URI($uri);

		$this->route = \Router::process($this);

		if ( ! $this->route)
		{
			return false;
		}

		if ($this->route->module !== null)
		{
			$this->module = $this->route->module;
			\Fuel::add_module($this->module);
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
			$this->output = static::show_404(true);
			return $this;
		}

		$controller_prefix = '\\'.($this->module ? ucfirst($this->module).'\\' : '').'Controller_';
		$method_prefix = 'action_';

		$class = $controller_prefix.($this->directory ? ucfirst($this->directory).'_' : '').ucfirst($this->controller);
		$method = $this->action;

		// If the class doesn't exist then 404
		if ( ! class_exists($class))
		{
			$this->output = static::show_404(true);
			return $this;
		}

		logger(Fuel::L_INFO, 'Loading controller '.$class, __METHOD__);
		$this->controller_instance = $controller = new $class($this);

		$method = $method_prefix.($method ?: (property_exists($controller, 'default_action') ? $controller->default_action : 'index'));


		// Allow to do in controller routing if method router(action, params) exists
		if (method_exists($controller, 'router'))
		{
			$method = 'router';
			$this->method_params = array($this->action, $this->method_params);
		}

		if (method_exists($controller, $method))
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
			$this->output =& $controller->output;
		}
		else
		{
			$this->output = static::show_404(true);
		}

		return $this;
	}

	public function send_headers()
	{
		\Output::send_headers();

		return $this;
	}

	public function output()
	{
		return $this->output;
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
		return (string) $this->output;
	}
}

/* End of file request.php */
