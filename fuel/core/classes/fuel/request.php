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

		$this->controller = $route['controller'];
		$this->action = $route['action'];
		$this->method_params = $route['method_params'];
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
		$class = $controller_prefix.ucfirst($this->controller);
		$method = 'action_'.$this->action;


		if (class_exists($class))
		{
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
		}
		else
		{
			static::show_404();
		}
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
