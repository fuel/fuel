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

namespace Fuel\Controller;

use Fuel\View;
use Fuel\Inflector;

/**
 * Template Controller class
 *
 * A base controller for easily creating templates.
 *
 * @package		Fuel
 * @category	Core
 * @author		Fuel Development Team
 */
abstract class Template extends Base {

	/**
	* @var string page template
	*/
	public $template = 'template';

	public $folder = '';

	/**
	* @var boolean auto render template
	**/
	public $auto_render = true;

	// Load the template and create the $this->template object
	public function before()
	{
		if ($this->auto_render === true)
		{
			if ($this->folder === '')
			{
				$this->folder = strtolower(Inflector::denamespace(get_called_class())).'/';
			}
			// Load the template
			$this->template = View::factory($this->folder.$this->template);
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
	
	public function add_partial($partial, $data = array())
	{
		$partial_name = str_replace(array('\\', '/'), '', $partial);
		
		$this->template->{'partial_'.$partial_name} = View::factory($this->folder.'_'.$partial, $data);
	}

	public function render($view, $data)
	{
		if ($this->auto_render === true)
		{
			$this->template->yield = View::factory($this->folder.$view, $data);
			return;
		}

		return View::factory($this->folder.$view, $data);
	}
}
/* End of file template.php */