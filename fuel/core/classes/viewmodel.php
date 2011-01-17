<?php

namespace Fuel\Core;

abstract class ViewModel {

	/**
	 * Factory for fetching the ViewModel
	 *
	 * @param	string	ViewModel classname without View_ prefix or full classname
	 * @param	string	Method to execute
	 * @return
	 */
	public static function factory($viewmodel, $method = 'view')
	{
		$class = 'View_'.$viewmodel;

		if ( ! class_exists($class))
		{
			if ( ! class_exists($class = $viewmodel))
			{
				throw new \Exception('ViewModel could not be found.');
			}
		}

		return new $class($method);
	}

	/**
	 * @var	Controller	reference to current controller
	 */
	protected $_controller;

	/**
	 * @var string	method to execute when rendering
	 */
	protected $_method;

	/**
	 * @var	string|View	view name, after instantiation a View object
	 */
	protected $_template = 'template';

	protected function __construct($method)
	{
		$this->_controller	= \Request::active()->controller_instance;
		$this->_template	= $this->set_template();
		$this->_method		= $method;

		$this->before();

		$this->_controller->output = $this;
	}

	protected function set_template()
	{
		return \View::factory($this->_template);
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
		$this->_template->{$name} = \Security::clean($val);
	}

	/**
	 * Sets a variable on the template without sanitizing
	 *
	 * @param	string
	 * @param	mixed
	 */
	public function set_raw($name, $val)
	{
		$this->_template->{$name} = $val;
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
		return $this->render();
	}
}