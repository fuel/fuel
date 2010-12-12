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

use Fuel\Application as App;

/**
 * Template Controller class
 *
 * A base controller for easily creating templates.
 *
 * @package		Fuel
 * @category	Core
 * @author		Fuel Development Team
 */
abstract class Template extends App\Controller\Base {

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
				$this->folder = strtolower(App\Inflector::denamespace(get_called_class())).'/';
			}
			// Load the template
			$this->template = App\View::factory($this->folder.$this->template);
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
		
		$this->template->{'partial_'.$partial_name} = App\View::factory($this->folder.'_'.$partial, $data);
	}

	public function render($view, $data)
	{
		$this->template->yield = App\View::factory($this->folder.$view, $data);
	}
}
/* End of file template.php */