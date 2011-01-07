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

namespace Oil;

/**
 * Oil\Scaffold Class
 *
 * @package		Fuel
 * @subpackage	Oil
 * @category	Core
 * @author		Dan Horrigan
 */
class Scaffold
{
	public function generate($args)
	{
		$g = new Generate;

		$g->model($args);

		$singular = strtolower(array_shift($args));
		$model_name = ucfirst($singular);
		$plural = \Inflector::pluralize($singular);

		$filepath = APPPATH.'classes/controller/'.$plural.'.php';
		$controller = new \View('scaffold/controller');

		$controller->name = $plural;

		$controller->model = $model_name;

		$controller->actions = array(
			array(
				'name'		=> 'index',
				'params'	=> '',
				'code'		=> '		$this->template->title = "'.ucfirst($plural).'";
		$this->template->'.strtolower($plural).' = '.$model_name.'::find(\'all\');',
			),
			array(
				'name'		=> 'view',
				'params'	=> '$id = 0',
				'code'		=> '		$this->template->title = "'.ucfirst($plural).'";
		$this->template->'.strtolower($singular).' = '.$model_name.'::find($id);',
			),
		);

		// Write controller
		if (self::write($filepath, $controller))
		{
			\Cli::write('Created controller');
		}
	}

	private function write($filepath, $data)
	{
		if ( ! $handle = @fopen($filepath, 'w+'))
		{
			throw new Exception('Cannot open file: '. $filepath);
		}

		$result = @fwrite($handle, $data);

		// Write $somecontent to our opened file.
		if ($result === FALSE)
		{
			throw new Exception('Cannot write to file: '. $filepath);
		}

		@fclose($handle);

		chmod($filepath, 0666);

		return $result;
	}

}

/* End of file oil/classes/scaffold.php */