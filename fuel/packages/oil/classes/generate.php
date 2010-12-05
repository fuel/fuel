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

		$plural = App\Inflector::pluralize($singular);

		$filepath = APPPATH . 'classes/controller/' . $singular .'.php';

		$class_name = 'Controller_' . ucfirst($singular);

		$action_str = '';

		// Create views folder
		static::views(array($plural, $args));

		foreach ($args as $action)
		{
			$action_str .= PHP_EOL."
	public function action_{$action}()
	{

	}";
		}

		// Build Controller
		$controller = <<<CONTROLLER
<?php

namespace Fuel\Application;

class {$class_name} extends Controller\Base {

{$action_str}

}

/* End of file $singular.php */
CONTROLLER;

		// Write controller
		if (self::write($filepath, $controller))
		{
			echo "Created model $singular";
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
		echo json_debug($args);
		exit;
		
		$folder = array_shift($args);
		$controller_title = App\Inflector::humanize($folder);

		if (empty($args))
		{
			$args = array('index');
		}

		$view_dir = APPPATH . 'views/'.$folder.'/';

		if ( ! is_dir($view_dir))
		{
			mkdir($view_dir, '0777');
		}

		foreach ($args as $action)
		{
			$view_title = App\Inflector::humanize($action);
			$view_filepath = Fuel::clean_path($view_dir . $action . '.php');

			$view = <<<VIEW
<h2>{$controller_title} &raquo; {$view_title}</h2>

<p>Edit this content in {$view_filepath}</p>
VIEW;

			if (self::write($view_dir . $action . '.php', $view))
			{

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

		return $result;
	}
}


/* End of file model.php */