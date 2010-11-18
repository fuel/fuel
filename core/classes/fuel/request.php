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

class Fuel_Request {

	/**
	 * @var	object	Holds the global request instance
	 */
	public static $instance = false;
	
	/**
	 * @var	object	Holds the global request instance
	 */
	public static $active = false;

	/**
	 * Returns the a Request object singleton
	 *
	 * @static
	 * @access	public
	 * @return	object
	 */
	public static function instance($uri = NULL)
	{
		if ( ! Request::$instance)
		{
			Request::$instance = Request::$active = new Request($uri);
		}

		return Request::$instance;
	}

	/**
	 * Returns the active request
	 *
	 * @static
	 * @access	public
	 * @return	object
	 */
	public static function active()
	{
		return Request::$active;
	}

	/**
	 * Shows a 404.  Checks to see if a 404_override route is set, if not show a default 404.
	 *
	 * @access	public
	 * @return	void
	 */
	public static function show_404()
	{
		if (Config::get('routes.404') === false)
		{
			Request::active()->output = View::factory('404');
		}
		else
		{
			list($controller, $action) = array_pad(explode('/', Config::get('routes.404')), 2, false);

			$action or $action = 'index';

			$class = 'Controller_'.$controller;
			$method = 'action_'.$action;

			if (class_exists($class))
			{
				$controller = new $class(Request::active());
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
					Request::active()->output =& $controller->output;
				}
				else
				{
					throw new Fuel_Exception('404 Action not found.');
				}
			}
			else
			{
				throw new Fuel_Exception('404 Controller not found.');
			}
		}
	}

	/**
	 * Generates a new request.  This is used for HMVC.
	 *
	 * @access	public
	 * @param	string	The URI of the request
	 * @return	object	The new request
	 */
	public static function factory($uri)
	{
		return new Request($uri);
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

	public function execute()
	{
		$class = 'Controller_'.ucfirst($this->controller);
		$method = 'action_'.$this->action;

		if (class_exists($class))
		{
			$controller = new $class($this);
			if (method_exists($controller, $method))
			{
				// Call the before method if it exists
				if (method_exists($controller, 'before'))
				{
					$controller->before();
				}

				call_user_func_array(array($controller, $method), $this->method_params);

				// Call the after method if it exists
				if (method_exists($controller, 'after'))
				{
					$controller->after();
				}

				// Get the controller's output
				$this->output =& $controller->output;
			}
			else
			{
				Request::show_404();
			}
		}
		else
		{
			Request::show_404();
		}
		return $this;
	}

	/**
	 * PHP magic function returns the Output of the request.
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