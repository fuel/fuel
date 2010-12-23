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
		logger(Fuel::L_INFO, 'Called', __METHOD__);

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
		logger(Fuel::L_INFO, 'Called', __METHOD__);

		App\Output::$status = 404;

		if (App\Config::get('routes.404') === null)
		{
			static::active()->output = App\View::factory('404');
		}
		else
		{
			list($controller, $action) = array_pad(explode('/', App\Config::get('routes.404')), 2, false);

			$action or $action = 'index';

			$class = 'Fuel\\App\\Controller\\'.ucfirst($controller);
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
					throw new App\Exception('404 Action not found.');
				}
			}
			else
			{
				throw new App\Exception('404 Controller not found.');
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
	 * Creates the new Request object by getting a new URI object, then parsing
	 * the uri with the Route class.
	 *
	 * @access	public
	 * @param	string	the uri string
	 * @return	void
	 */
	public function __construct($uri)
	{
		$this->uri = new App\URI($uri);
		$route = App\Route::parse($this->uri);

		// Attempts to register the first segment as a module
		$mod_path = App\Fuel::add_module($route['segments'][0], true);

		if ($mod_path !== false)
		{
			$this->module = array_shift($route['segments']);
		}

		// Check for directory
		$path = ( ! empty($this->module) ? $mod_path : APPPATH).'classes'.DS.'controller'.DS;
		if ( ! empty($route['segments']) && is_dir($dirpath = $path.strtolower($route['segments'][0])))
		{
			$this->directory = array_shift($route['segments']);
		}

		// When emptied the controller defaults to directory or module
		$controller = empty($this->directory) ? $this->module : $this->directory;
		if (count($route['segments']) == 0)
		{
			$route['segments'] = array($controller);
		}

		$this->controller = $route['segments'][0];
		$this->action = isset($route['segments'][1]) ? $route['segments'][1] : '';
		$this->method_params = array_slice($route['segments'], 2);
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
		logger(Fuel::L_INFO, 'Called', __METHOD__);

		$controller_prefix = 'Fuel\\App\\'.($this->module ? ucfirst($this->module).'\\' : '').'Controller\\';
		$method_prefix = 'action_';

		$class = $controller_prefix.($this->directory ? ucfirst($this->directory).'_' : '').ucfirst($this->controller);
		$method = $this->action;

		// Allow omitting the controller name when in an equally named directory or module
		if ( ! class_exists($class))
		{
			// set the new controller to directory or module when applicable
			$controller = $this->directory ?: $this->module;
			// ... or to the default controller if it was in neither
			$controller = $controller ?: preg_replace('#/([a-z0-9/_]*)$#uiD', '', App\Route::$routes['#']);

			// try again with new controller if it differs from the previous attempt
			if ($controller != $this->controller)
			{
				$class = $controller_prefix.($this->directory ? $this->directory.'_' : '').ucfirst($controller);
				array_unshift($this->method_params, $this->action);
				$this->action = $this->controller;
				$method = $this->action ?: '';
				$this->controller = $controller;
			}

			// 404 if it's still not found
			if ( ! class_exists($class))
			{
				static::show_404();
				return $this;
			}
		}

		logger(Fuel::L_INFO, 'Loading controller '.$class, __METHOD__);
		$controller = new $class($this);

		$method = $method_prefix.($method ?: (@$controller->default_action ?: 'index'));


		// Allow to do in controller routing if method router(action, params) exists
		if (method_exists($controller, 'router'))
		{
			$method = 'router';
			$this->method_params = array($this->action, $this->method_params);
		}

		// Call the before method if it exists
		if (method_exists($controller, 'before'))
		{
			logger(Fuel::L_INFO, 'Calling '.$class.'::before', __METHOD__);
			$controller->before();
		}

		if (method_exists($controller, $method))
		{
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
			static::show_404();
		}

		return $this;
	}

	public function send_headers()
	{
		App\Output::send_headers();

		return $this;
	}

	public function output()
	{
		echo $this->output;
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
