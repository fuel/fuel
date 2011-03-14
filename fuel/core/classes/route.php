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

class Route {

	public $segments = array();

	public $named_params = array();

	public $method_params = array();

	public $path = '';
	
	public $module = null;

	public $directory = null;

	public $controller = null;

	public $action = 'index';
	
	public $translation = null;

	protected $search = null;

	public function __construct($path, $translation = null)
	{
		if ($translation === null)
		{
			$this->path = $path;
			$this->translation = $path;
		}
		else
		{
			$this->path = $path;
			$this->translation = $translation;
			$this->compile();
		}
	}

	protected function compile()
	{
		if ($this->path === '_root_')
		{
			$this->search = '';
		}
		else
		{
			$this->search = str_replace(array(':any', ':segment'), array('.+', '[^/]+([^/]*)'), $this->path);
			$this->search = preg_replace('|:([a-z\_]+)|uD', '(?P<$1>.+)', $this->search);
		}
	}

	/**
	 * Attemptes to find the correct route for the given URI
	 *
	 * @access	public
	 * @param	object	The URI object
	 * @return	array
	 */
	public function parse(\Request $request)
	{
		$uri = $request->uri->get();

		if ($uri === '' and $this->path === '_root_')
		{
			return $this->matched();
		}

		$result = $this->_parse_search($uri);

		if ($result)
		{
			return $result;
		}

		return false;
	}

	/**
	 * Parses a route match and returns the controller, action and params.
	 *
	 * @access	public
	 * @param	string	The matched route
	 * @return	array
	 */
	public function matched($uri = '', $named_params = array())
	{
		$path = $this->translation;

		if ($uri != '')
		{
			$path = preg_replace('@^'.$this->search.'$@uD', $this->translation, $uri);
		}

		$method_params = array();

		// Clean out all the non-named stuff out of $named_params
		foreach($named_params as $key => $val)
		{
			if (is_numeric($key))
			{
				unset($named_params[$key]);
			}
		}

		$this->named_params = $named_params;
		$this->segments = explode('/', trim($path, '/'));

		return $this;
	}

	/**
	 * Parses an actual route - extracted out of parse() to make it recursive.
	 *
	 * @access private
	 * @param string The URI object
	 * @return array OR boolean
	 */
	private function _parse_search($uri, $route = null)
	{
		if ($route === null)
		{
			$route =& $this;
		}

		if (is_array($route->translation))
		{
			foreach ($route->translation as $r)
			{
				$verb = $r[0];

				if (\Input::method() == strtoupper($verb))
				{
					$r[1]->search = $route->search;
					$result = $route->_parse_search($uri, $r[1]);

					if ($result)
					{
						return $result;
					}
				}
			}

			return false;
		}

		if (preg_match('@^'.$route->search.'$@uD', $uri, $params) != false)
		{
			return $route->matched($uri, $params);
		}
		else
		{
			return false;
		}
	}
}

/* End of file route.php */
