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

use Fuel\Application as App;

class Generate
{
	public function controller($args)
	{
		$singular = strtolower(array_shift($args));
		$actions = $args;
		
		$plural = App\Inflector::pluralize($singular);

		$filepath = APPPATH . 'classes/controller/' . $plural .'.php';

		$class_name = 'Controller_' . ucfirst($plural);

		// Stick "blogs" to the start of the array
		array_unshift($args, $plural);

		// Create views folder and each view file
		static::views($args);

		$action_str = '';
		foreach ($actions as $action)
		{
			$action_str .= '
	public function action_'.$action.'()
	{
		$this->template->title = \'' . App\Inflector::humanize($plural) .' &raquo ' . App\Inflector::humanize($action) . '\';
		$this->template->content = View::factory(\''.$plural .'/' . $action .'\');
	}'.PHP_EOL;
		}

		// Build Controller
		$controller = <<<CONTROLLER
<?php

namespace Fuel\Application;

class {$class_name} extends Controller\Template {
{$action_str}
}

/* End of file $singular.php */
CONTROLLER;

		// Write controller
		if (self::write($filepath, $controller))
		{
			echo "Created controller $plural";
		}
	}

	
	public function model($args)
	{
		$singular = strtolower(array_shift($args));

		$plural = App\Inflector::pluralize($singular);

		$filepath = APPPATH . 'classes/model/' . $singular .'.php';

		$class_name = 'Model_' . ucfirst($singular);

		$model = <<<MODEL
<?php

namespace Fuel\Application;

use ActiveRecord;

class {$class_name} extends ActiveRecord\Model {

/* End of file $singular.php */
MODEL;

		if (self::write($filepath, $model))
		{
			echo "Created model $singular";
		}
	}


	public function views($args)
	{
		$folder = array_shift($args);
		$controller_title = App\Inflector::humanize($folder);

		if (empty($args))
		{
			$args = array('index');
		}

		// Make the directory for these views to be store in
		if ( ! is_dir($view_dir = APPPATH . 'views/'.$folder.'/'))
		{
			mkdir($view_dir, 0777);
		}

		// Add the default template if it doesnt exist
		if ( ! file_exists($app_template = APPPATH . 'views/template.php'))
		{
			copy(PKGPATH . 'oil/views/template.php', $app_template);
			chmod($app_template, 0666);
		}
		unset($app_template);

		foreach ($args as $action)
		{
			$view_title = App\Inflector::humanize($action);
			$view_filepath = App\Fuel::clean_path($view_file = $view_dir . $action . '.php');

			$view = <<<VIEW
<p>Edit this content in {$view_filepath}</p>
VIEW;

			if (self::write($view_file, $view))
			{
				echo "\tCreated view: {$view_file}".PHP_EOL;
			}
		}
	}


	public function help()
	{
		echo <<<HELP
Usage:
  php oil generate [controller|model|migration|view|views] [options]

Runtime options:
  -f, [--force]    # Overwrite files that already exist
  -s, [--skip]     # Skip files that already exist
  -p, [--pretend]  # Run but do not make any changes
  -q, [--quiet]    # Supress status output

Fuel options:
  -v, [--version]  # Show Fuel version number and quit

Description:
    The 'oil' command can be used to generate MVC components, database migrations
    and run specific tasks.

Examples:
    php oil generate controller <controllername> [<action1> |<action2> |..]
    php oil g model <modelname> [<fieldname1>:<type1> |<fieldname2>:<type2> |..]
HELP;
	}

	public function write($filepath, $data)
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


/* End of file model.php */