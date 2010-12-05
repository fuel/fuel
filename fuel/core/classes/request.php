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

use Fuel\Application as App;

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
		App\Log::info('Creating a new Request with URI = "'.$uri.'"', __METHOD__);

		static::$active = new static($uri);

		if ( ! static::$main)
		{
			App\Log::info('Setting main Request', __METHOD__);
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
		App\Log::info('Called', __METHOD__);

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
		App\Log::info('Called', __METHOD__);

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
		App\Log::info('Called', __METHOD__);

		if (Config::get('routes.404') === false)
		{
			static::active()->output = App\View::factory('404');
		}
		else
		{
			list($controller, $action) = array_pad(explode('/', Config::get('routes.404')), 2, false);

			$action or $action = 'index';

			$class = 'Fuel\\Application\\Controller_'.ucfirst($controller);
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

		// Register module as such when found
		$mod_path = App\Fuel::add_module($route['segments'][0], true);
		if ( ! empty($mod_path))
		{
			$this->module = array_shift($route['segments']);

			// Optionally load module routes & reparse, must be relative to module
			//     so it only allows routing within module
			if (is_file($route_path = $mod_path.'config'.DS.'routes.php'))
			{
				$route = App\Route::parse_module($this->module, App\Fuel::load($route_path), $this->uri);
			}

			// Does the module need always_loading?
			if (is_file($always_load_path = $mod_path.'config'.DS.'always_load.php'))
			{
				App\Fuel::always_load(Fuel::load($always_load_path));
			}
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
		$this->action = ! empty($route['segments'][1]) ? $route['segments'][1] : '';
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
		App\Log::info('Called', __METHOD__);

		$controller_prefix = 'Fuel\\Application\\Controller_';
		$method_prefix = 'action_';

		$class = $controller_prefix.(empty($this->directory) ? '' : \ucfirst($this->directory).'_').ucfirst($this->controller);
		$method = $this->action ?: '';

		// Allow omitting the controller name when in an equally named directory or module
		if ( ! class_exists($class))
		{
			// set the new controller to directory or module when applicable
			$controller = empty($this->directory) ? $this->module : $this->directory;
			// ... or to the default controller if it was in neither
			$controller = empty($controller) ? preg_replace('#/([a-z0-9/_]*)$#uiD', '', App\Route::$routes['#']) : $controller;

			// try again with new controller if it differs from the previous attempt
			if ($controller != $this->controller)
			{
				$class = $controller_prefix.(empty($this->directory) ? '' : $this->directory.'_').ucfirst($controller);
				array_unshift($this->method_params, $this->action);
				$this->action = $this->controller;
				$method = $this->action ?: '';
				$this->controller = $controller;

				// attempt autoload
				class_exists($class);
			}

			// 404 if it's still not found
			if ( ! class_exists($class, false))
			{
				static::show_404();
				return $this;
			}
		}

		App\Log::info('Loading controller '.$class, __METHOD__);
		$controller = new $class($this);

		$method = $method_prefix.($method ?: (isset($controller->default_action) ? $controller->default_action : 'index'));

		// Allow to do in controller routing if method router(action, params) exists
		if (method_exists($controller, 'router'))
		{
			$method = 'router';
			$this->method_params = array($this->action, $this->method_params);
		}

		// Call the before method if it exists
		if (method_exists($controller, 'before'))
		{
			App\Log::info('Calling '.$class.'::before', __METHOD__);
			$controller->before();
		}

		if (method_exists($controller, $method))
		{
			App\Log::info('Calling '.$class.'::'.$method, __METHOD__);
			call_user_func_array(array($controller, $method), $this->method_params);

			// Call the after method if it exists
			if (method_exists($controller, 'after'))
			{
				App\Log::info('Calling '.$class.'::after', __METHOD__);
				$controller->after();
			}

			// Get the controller's output
			$this->output =& $controller->output;

			return $this;
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