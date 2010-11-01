<?php defined('SYSPATH') or die('No direct script access.');
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

class Fuel_Controller {

	public $request;
	
	public $output;

	public function __construct(Fuel_Request $request)
	{
		$this->request = $request;
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

}

/* End of file fuel_controller.php */