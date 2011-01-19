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
 * @copyright	2010 - 2011 Fuel Development Team
 * @link		http://fuelphp.com
 */

namespace Fuel\Core;

/**
 * AJAX Template Controller class
 *
 * If a request is AJAX, the template view is not rendered but only the content is
 *
 * @package		Fuel
 * @category	Core
 * @author		Tom Arnfeld (@tarnfeld)
 */
abstract class Controller_Template_Ajax extends \Controller_Template {

	// After contorller method has run output the template if the request is not ajax
	public function after()
	{
		
		parent::after();
		
		if ($this->auto_render === true)
		{
			if(Input::is_ajax()) {
				$this->output = $this->template->content;
			} else {
				$this->output = $this->template;
			}
		}
	}

}
/* End of file template.php */