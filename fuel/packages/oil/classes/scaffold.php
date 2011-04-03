<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2011 Fuel Development Team
 * @link       http://fuelphp.com
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
	public static function _init()
	{
		Generate::$scaffolding = true;
	}

	public static function generate($args, $subfolder = 'default')
	{
		$subfolder = trim($subfolder, '/');
		if ( ! is_dir( PKGPATH.'oil/views/'.$subfolder))
		{
			throw new Exception('The subfolder for scaffolding templates doesn\'t exist or is spelled wrong: '.$subfolder.' ');
		}

		// Do this first as there is the largest chance of error here
		Generate::model($args, false);

		// Go through all arguments after the first and make them into field arrays
		$fields = array();
		foreach (array_slice($args, 1) as $arg)
		{
			// Parse the argument for each field in a pattern of name:type[constraint]
			preg_match('/([a-z0-9_]+):([a-z0-9_]+)(\[([0-9]+)\])?/i', $arg, $matches);

			$fields[] = array(
				'name' => strtolower($matches[1]),
				'type' => isset($matches[2]) ? $matches[2] : 'string',
				'constraint' => isset($matches[4]) ? $matches[4] : null
			);
		}

		$data['singular'] = $singular = strtolower(array_shift($args));
		$data['model'] = $model_name = 'Model_'.Generate::class_name($singular);
		$data['plural'] = $plural = \Inflector::pluralize($singular);
		$data['fields'] = $fields;

		$filepath = APPPATH.'classes/controller/'.trim(str_replace(array('_', '-'), DS, $plural), DS).'.php';
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
		Generate::create($filepath, $controller, 'controller');

		// Create each of the views
		foreach (array('index', 'view', 'create', 'edit', '_form') as $view)
		{
			Generate::create(APPPATH.'views/'.$plural.'/'.$view.'.php', \View::factory($subfolder.'/scaffold/views/'.$view, $data), 'view');
		}

		// Add the default template if it doesnt exist
		if ( ! file_exists($app_template = APPPATH . 'views/template.php'))
		{
			Generate::create($app_template, file_get_contents(PKGPATH . 'oil/views/default/template.php'), 'view');
		}

		Generate::build();
	}

}

/* End of file oil/classes/scaffold.php */