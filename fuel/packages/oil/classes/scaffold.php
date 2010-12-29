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

namespace Oil;

use Fuel\App as App;

class Scaffold
{
	public function generate($args)
	{
		$g = new Generate;

		$g->model($args);

		$singular = strtolower(array_shift($args));
		$model_name = ucfirst($singular);
		$plural = App\Inflector::pluralize($singular);

		$filepath = APPPATH.'classes/controller/'.$plural.'.php';
		$controller = new App\View('scaffold/controller');

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
			echo "Created controller".PHP_EOL;
		}
	}

	private function write($filepath, $data)
	{
		if ( ! $handle = @fopen($filepath, 'w+'))
		{
			throw new App\Exception('Cannot open file: '. $filepath);
		}

		$result = @fwrite($handle, $data);

		// Write $somecontent to our opened file.
		if ($result === FALSE)
		{
			throw new App\Exception('Cannot write to file: '. $filepath);
		}

		@fclose($handle);

		chmod($filepath, 0666);

		return $result;
	}

}

/* End of file scaffold.php */
