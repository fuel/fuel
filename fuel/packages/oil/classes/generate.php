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

class Generate
{
	private static $_default_constraints = array(
		'varchar' => 255,
		'int' => 11
	);

	public function controller($args)
	{
		$args = self::_clear_args($args);
		$singular = strtolower(array_shift($args));
		$actions = $args;
		
		$plural = App\Inflector::pluralize($singular);

		$filepath = APPPATH . 'classes/controller/' . $plural .'.php';

		$class_name = ucfirst($plural);

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

namespace Fuel\App\Controller;
use Fuel\Core\Controller;

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

		$class_name = ucfirst($singular);

		$model = <<<MODEL
<?php

namespace Fuel\App\Model;

use ActiveRecord;

class {$class_name} extends ActiveRecord\Model { }

/* End of file $singular.php */
MODEL;

		if (self::write($filepath, $model))
		{
			echo "Created model: " . App\Fuel::clean_path($filepath).PHP_EOL;
		}

		if ( ! empty($args))
		{
			array_unshift($args, 'create_'.$plural);
			static::migration($args);
		}
	}


	public function views($args)
	{
		$args = self::_clear_args($args);
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


	public function migration($args)
	{
		$migration_name = strtolower(array_shift($args));

		// Starts with create, so lets create a table
		if (strpos($migration_name, 'create_') === 0)
		{
			$mode = 'create_table';
			$table = str_replace('create_', '', $migration_name);
		}

		// add_field_to_table
		else if (strpos($migration_name, 'add_') === 0)
		{
			$mode = 'add_fields';

			preg_match('/add_[a-z0-9_]+_to_([a-z0-9_]+)/i', $migration_name, $matches);
			
			$table = $matches[1];
		}

		// remove_field_from_table
		else if (strpos($migration_name, 'remove_') === 0)
		{
			$mode = 'remove_field';

			preg_match('/remove_([a-z0-9_])+_from_([a-z0-9_]+)/i', $migration_name, $matches);

			$remove_field = $matches[1];
			$table = $matches[2];
		}

		// drop_table
		else if (strpos($migration_name, 'drop_') === 0)
		{
			$mode = 'drop_table';
			$table = str_replace('drop_', '', $migration_name);
		}
		unset($matches);

		// Now that we know that, lets build the migration

		if ($filepath = static::_build_migration($migration_name, $mode, $table, $args))
		{
			echo "Created migration: " . App\Fuel::clean_path($filepath).PHP_EOL;
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


	// Helper functions


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


	private function _build_migration($migration_name, $mode, $table, $args)
	{
		$migration_name = ucfirst(strtolower($migration_name));

		if ($mode == 'create_table' or $mode == 'add_fields')
		{
			$field_str = '';

			foreach ($args as $arg)
			{
				// Parse the argument for each field in a pattern of name:type[constraint]
				preg_match('/([a-z0-9_]+):([a-z0-9_]+)(\[([0-9]+)\])?/i', $arg, $matches);

				$name = $matches[1];
				$type = $matches[2];
				$constraint = isset($matches[4]) ? $matches[4] : null;

				if ($type === 'string')
				{
					$type = 'varchar';
				}

				if (in_array($type, array('text', 'blob', 'datetime')))
				{
					$field_str .= "\t\t\t'$name' => array('type' => '$type'),".PHP_EOL;
				}

				else
				{
					if ($constraint === null)
					{
						$constraint = self::$_default_constraints[$type];
					}

					$field_str .= "\t\t\t'$name' => array('type' => '$type', 'constraint' => $constraint),".PHP_EOL;
				}
			}

		}

		// Make the up and down based on mode
		switch ($mode)
		{
			case 'create_table':
				$up = <<<UP
		DBUtil::create_table('{$table}', array(
$field_str
		), array('id'));
UP;

				$down = <<<DOWN
		DBUtil::drop_table('{$table}');
DOWN;
			break;

			case 'drop_table':
				$up = <<<UP
		DBUtil::drop_table('{$table}');
UP;

				$down = '';
			break;

			default:
				$up = '';
				$down = '';
		}


		$migration = <<<MIGRATION
<?php

namespace Fuel\App;

class Migration_{$migration_name} extends Migration {

	function up()
	{
{$up}
	}

	function down()
	{
{$down}
	}
}
MIGRATION;

		$number = self::_find_migration_number();
		$filepath = APPPATH . 'migrations/'.$number.'_' . strtolower($migration_name) . '.php';

		if (glob(APPPATH .'migrations/*_' . strtolower($migration_name) . '.php'))
		{
			throw new App\Exception('A migration with this name already exists.');
		}

		if (self::write($filepath, $migration))
		{
			self::_update_current_version(intval($number));
			return $filepath;
		}


		return false;
	}


	private function _find_migration_number()
	{
		list($last) = explode('_', basename(end(glob(APPPATH .'migrations/*_*.php'))));

		return str_pad($last + 1, 3, '0', STR_PAD_LEFT);
	}

	private function _update_current_version($version)
	{
		$contents = '';
		$path = '';
		if (file_exists($path = APPPATH.'config'.DS.'migration.php'))
		{
			$contents = file_get_contents($path);
		}
		elseif (file_exists($path = COREPATH.'config'.DS.'migration.php'))
		{
			$contents = file_get_contents($path );
		}

		$contents = preg_replace("#('version'[ \t]+=>)[ \t]+([0-9]+),#i", "$1 $version,", $contents);

		self::write($path, $contents);
	}

	private function _clear_args($actions = array())
	{
 		foreach ($actions as $key => $action) {
		if (substr($action, 0, 1) === '-')
			unset($actions[$key]);
        }
        
		return $actions;
	}		
}

/* End of file model.php */
