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



class Controller {

	/**
	 * @var	object	The current Request object
	 */
	public $request;

	/**
	 * @var	object	The current Response object
	 */
	public $response;

	/**
	 * Sets the controller request object.
	 *
	 * @access	public
	 * @param	object	The current request object
	 * @return	void
	 */
	public function __construct(\Request $request, \Response $response)
	{
		$this->request = $request;
		$this->response = $response;
	}

	/**
	 * This method gets called before the action is called
	 *
	 * @access	public
	 * @return	void
	 */
	public function before() { }

	/**
	 * This method gets called after the action is called
	 *
	 * @access	public
	 * @return	void
	 */
	public function after() { }

	/**
	 * This method returns the named parameter requested, or all of them
	 * if no parameter is given.
	 *
	 * @access	public
	 * @param	string	The name of the parameter
	 * @return	void
	 */
	public function param($param)
	{
		if ( ! isset($this->request->named_params[$param]))
		{
			return FALSE;
		}

		return $this->request->named_params[$param];
	}

	/**
	 * This method returns all of the named parameters.
	 *
	 * @access	public
	 * @return	void
	 */
	public function params()
	{
		return $this->request->named_params;
	}

	public function render($view, $data = array(), $auto_encode = null)
	{
		$this->response->body .= \View::factory($view, $data, $auto_encode);
	}
}

/* End of file controller.php */