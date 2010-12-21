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
 * @copyright	2010 Dan Horrigan
 * @link		http://fuelphp.com
 */

namespace Fuel\Core\Controller;

use Fuel\App;

/**
 * Template Controller class
 *
 * A base controller for easily creating templates.
 *
 * @package		Fuel
 * @category	Core
 * @author		Fuel Development Team
 */
abstract class TemplateController extends App\Controller\BaseController {

	/**
	* @var string page template
	*/
	public $template = 'template';

	/**
	* @var boolean auto render template
	**/
	public $auto_render = true;

	// Load the template and create the $this->template object
	public function before()
	{
		if ($this->auto_render === true)
		{
			// Load the template
			$this->template = App\View::factory($this->template);
		}

		return parent::before();
	}

	// After contorller method has run output the template
	public function after()
	{
		if ($this->auto_render === true)
		{
			$this->output = $this->template;
		}

		return parent::after();
	}

}
/* End of file template.php */
