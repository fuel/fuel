<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Carbon
 *
 * Carbon is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Carbon
 * @version		1.0
 * @author		Dan Horrigan <http://dhorrigan.com>
 * @license		Apache License v2.0
 * @copyright	2010 Dan Horrigan
 */

class Carbon_Controller {

	public $request;

	public function __construct(Carbon_Request $request)
	{
		$this->request = $request;
	}

}

/* End of file carbon_controller.php */