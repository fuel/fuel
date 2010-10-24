<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Dan Horrigan <http://dhorrigan.com>
 * @license		Apache License v2.0
 * @copyright	2010 Dan Horrigan
 */

class Fuel_Controller {

	public $request;

	public function __construct(Fuel_Request $request)
	{
		$this->request = $request;
	}

}

/* End of file fuel_controller.php */