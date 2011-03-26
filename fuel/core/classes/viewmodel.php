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



// ------------------------------------------------------------------------

/**
 * ViewModel
 *
 * @package	    Fuel
 * @subpackage  Core
 * @category    Core
 * @author      Jelmer Schreuder
 */
abstract class ViewModel {

	/**
	 * Factory for fetching the ViewModel
	 *
	 * @param   string  ViewModel classname without View_ prefix or full classname
	 * @param   string  Method to execute
	 * @return  ViewModel
	 */
	public static function factory($viewmodel, $method = 'view')
	{
		$class = ucfirst(\Request::active()->module).'\\View_'.ucfirst(str_replace(DS, '_', $viewmodel));

		if ( ! class_exists($class))
		{
			if ( ! class_exists($class = $viewmodel))
			{
				throw new \Fuel_Exception('ViewModel could not be found.');
			}
		}

		return new $class($method);
	}

	/**
	 * @var  string  method to execute when rendering
	 */
	protected $_method;

	/**
	 * @var  string|View  view name, after instantiation a View object
	 */
	protected $_template;

	/**
	 * @var  bool  whether or not to use auto encoding
	 */
	protected $_auto_encode;

	protected function __construct($method)
	{
		if (empty($this->_template))
		{
			$class = get_class($this);
			$this->_template = strtolower(str_replace('_', '/', preg_replace('#^([a-z0-9_]*\\\\)?(View_)?#i', '', $class)));
		}

		$this->_template	= $this->set_template();
		$this->_method		= $method;
		$this->_auto_encode = \View::$auto_encode;

		$this->before();

		// Set this as the controller output if this is the first ViewModel loaded
		if ( ! \Request::active()->controller_instance->response->body instanceof ViewModel)
		{
			\Request::active()->controller_instance->response->body = $this;
		}
	}

	/**
	 * Must return a View object or something compatible
	 *
	 * @return	Object	any object on which the template vars can be set and which has a toString method
	 */
	protected function set_template()
	{
		return \View::factory($this->_template);
	}

	/**
	 * Change auto encoding setting
	 *
	 * @param   null|bool  change setting (bool) or get the current setting (null)
	 * @return  void|bool  returns current setting or nothing when it is changed
	 */
	public function auto_encoding($setting = null)
	{
		if (is_null($setting))
		{
			return $this->_auto_encode;
		}

		$this->_auto_encode = (bool) $setting;
	}

	/**
	 * Executed before the view method
	 */
	public function before() {}

	/**
	 * The default view method
	 * Should set all expected variables upon itself
	 */
	public function view() {}

	/**
	 * Executed after the view method
	 */
	public function after() {}

	/**
	 * Fetches an existing value from the template
	 *
	 * @return	mixed
	 */
	public function __get($name)
	{
		return $this->_template->{$name};
	}

	/**
	 * Sets and sanitizes a variable on the template
	 *
	 * @param	string
	 * @param	mixed
	 */
	public function __set($name, $val)
	{
		\View::$auto_encode ? $this->set_safe($name, $val) : $this->set_raw($name, $val);
	}

	/**
	 * Sets a variable on the template without sanitizing
	 * Note: Objects are auto-converted to strings unless they're ViewModel, View or Closure instances, if you want
	 * 		objects not to be converted add them through set_raw().
	 *
	 * @param	string
	 * @param	mixed
	 */
	public function set_safe($name, $val)
	{
		$this->_template->set($name, $val, true);
	}

	/**
	 * Sets a variable on the template without sanitizing
	 *
	 * @param	string
	 * @param	mixed
	 */
	public function set_raw($name, $val)
	{
		$this->_template->set($name, $val, false);
	}

	/**
	 * Add variables through method and after() and create template as a string
	 */
	public function render()
	{
		$this->{$this->_method}();
		$this->after();

		return (string) $this->_template;
	}

	/**
	 * Auto-render on toString
	 */
	public function __toString()
	{
		try
		{
			return $this->render();
		}
		catch (\Exception $e)
		{
			\Error::exception_handler($e);

			return '';
		}
	}
}

/* End of file viewmodel.php */
