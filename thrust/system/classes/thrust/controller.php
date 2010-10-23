<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Thrust
 *
 * Thrust is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Thrust
 * @version		1.0
 * @author		Dan Horrigan <http://dhorrigan.com>
 * @license		Apache License v2.0
 * @copyright	2010 Dan Horrigan
 */

class Thrust_Controller {

	public $request;

	public function __construct(Thrust_Request $request)
	{
		$this->request = $request;
	}

}

/* End of file thrust_controller.php */