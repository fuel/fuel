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
	public function generate($args, $subfolder = 'default')
	{
		$subfolder = trim($subfolder, '/');
		if( ! is_dir( PKGPATH.'oil/views/'.$subfolder))
		{
			throw new Exception('The subfolder for scaffolding templates doesn\'t exist or is spelled wrong: '.$subfolder.' ');
		}
	
		// Do this first as there is the largest chance of error here
		Generate::model($args);
		
		// Go through all arguments after the first and make them into field arrays
		$fields = array();
		foreach (array_slice($args, 1) as $arg)
		{
			// Parse the argument for each field in a pattern of name:type[constraint]
			preg_match('/([a-z0-9_]+):([a-z0-9_]+)(\[([0-9]+)\])?/i', $arg, $matches);

			$fields[] = array(
				'name' => strtolower($matches[1]),
				'type' => $matches[2],
				'constraint' => isset($matches[4]) ? $matches[4] : null
			);
		}

		$data['singular'] = $singular = strtolower(array_shift($args));
		$data['model'] = $model_name = 'Model_' . ucfirst($singular);
		$data['plural'] = $plural = \Inflector::pluralize($singular);
		$data['fields'] = $fields;

		$filepath = APPPATH . 'classes/controller/' . $plural . '.php';
		$controller = \View::factory($subfolder.'/scaffold/controller', $data);

		$controller->actions = array(
			array(
				'name'		=> 'index',
				'params'	=> '',
				'code'		=> \View::factory($subfolder.'/scaffold/actions/index', $data),
			),
			array(
				'name'		=> 'view',
				'params'	=> '$id = null',
				'code'		=> \View::factory($subfolder.'/scaffold/actions/view', $data),
			),
			array(
				'name'		=> 'create',
				'params'	=> '$id = null',
				'code'		=> \View::factory($subfolder.'/scaffold/actions/create', $data),
			),
			array(
				'name'		=> 'edit',
				'params'	=> '$id = null',
				'code'		=> \View::factory($subfolder.'/scaffold/actions/edit', $data),
			),
			array(
				'name'		=> 'delete',
				'params'	=> '$id = null',
				'code'		=> \View::factory($subfolder.'/scaffold/actions/delete', $data),
			),
		);

		// Write controller
		if (self::write($filepath, $controller))
		{
			\Cli::write('Created controller: ' . \Fuel::clean_path($filepath));
		}

		// Add the default template if it doesnt exist
		if ( ! file_exists($app_template = APPPATH . 'views/template.php'))
		{
			copy(PKGPATH . 'oil/views/'.$subfolder.'/template.php', $app_template);
			chmod($app_template, 0666);
		}
		
		// Create view folder if not already there
		if ( ! is_dir($view_folder = APPPATH . 'views/' . $plural . '/'))
		{
			mkdir(APPPATH . 'views/' . $plural, 0755);
		}

		// Create each of the views
		foreach (array('index', 'view', 'create', 'edit', '_form') as $view)
		{
			static::write($view_file = $view_folder . $view . '.php', \View::factory($subfolder.'/scaffold/views/'.$view, $data));

			\Cli::write('Created view: ' . \Fuel::clean_path($view_file));
		}
	}

	private function write($filepath, $data)
	{
		if ( ! $handle = fopen($filepath, 'w+'))
		{
			throw new Exception('Cannot open file: '. \Fuel::clean_path($filepath));
		}

		$result = @fwrite($handle, $data);

		// Write $somecontent to our opened file.
		if ($result === FALSE)
		{
			throw new Exception('Cannot write to file: '. \Fuel::clean_path($filepath));
		}

		@fclose($handle);

		chmod($filepath, 0666);

		return $result;
	}

}

/* End of file oil/classes/scaffold.php */