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
 * Oil\Generate Class
 *
 * @package		Fuel
 * @subpackage	Oil
 * @category	Core
 * @author		Phil Sturgeon
 */
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

		$filepath = APPPATH . 'classes/controller/' . $singular .'.php';

		$class_name = ucfirst($singular);

		// Stick "blogs" to the start of the array
		array_unshift($args, $singular);

		// Create views folder and each view file
		static::views($args);

        if (empty($actions))
		{
			$actions = array('index');
		}
        
		$action_str = '';
		foreach ($actions as $action)
		{
			$action_str .= '
	public function action_'.$action.'()
	{
		$this->template->title = \'' . \Inflector::humanize($singular) .' &raquo ' . \Inflector::humanize($action) . '\';
		$this->template->content = View::factory(\''.$singular .'/' . $action .'\');
	}'.PHP_EOL;
		}

		// Build Controller
		$controller = <<<CONTROLLER
<?php

class Controller_{$class_name} extends Controller_Template {
{$action_str}
}

/* End of file $singular.php */
CONTROLLER;

		// Write controller
		if (self::write($filepath, $controller))
		{
			\Cli::write('Created controller: ' . $filepath);
		}
	}


	public function model($args)
	{
		$singular = strtolower(array_shift($args));

		$plural = \Inflector::pluralize($singular);

		$filepath = APPPATH . 'classes/model/' . $singular .'.php';

		$class_name = ucfirst($singular);

		$model = <<<MODEL
<?php

class Model_{$class_name} extends ActiveRecord\Model { }

/* End of file $singular.php */
MODEL;

		if (self::write($filepath, $model))
		{
			\Cli::write('Created model: ' . \Fuel::clean_path($filepath));
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
		$controller_title = \Inflector::humanize($folder);

		empty($args) and $args = array('index');

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
			$view_title = \Inflector::humanize($action);
//			$view_filepath = \Fuel::clean_path($view_file = $view_dir . $action . '.php');
			$view_filepath = $view_file = $view_dir . $action . '.php';

			$view = <<<VIEW
<p>Edit this content in {$view_filepath}</p>
VIEW;

			if (self::write($view_file, $view))
			{
				\Cli::write("\tCreated view: " . $view_file);
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
			\Cli::write('Created migration: ' . \Fuel::clean_path($filepath));
		}
	}


	public function help()
	{
		$output = <<<HELP
Usage:
  php oil [g|generate] [controller|model|migration|scaffold|views] [options]

Runtime options:
  -f, [--force]    # Overwrite files that already exist
  -s, [--skip]     # Skip files that already exist

Description:
  The 'oil' command can be used to generate MVC components, database migrations
  and run specific tasks.

Examples:
  php oil generate controller <controllername> [<action1> |<action2> |..]
  php oil g model <modelname> [<fieldname1>:<type1> |<fieldname2>:<type2> |..]
  php oil g migration <migrationname> [<fieldname1>:<type1> |<fieldname2>:<type2> |..]
  php oil g scaffold <modelname> [<fieldname1>:<type1> |<fieldname2>:<type2> |..]

Documentation:
  http://fuelphp.com/docs/packages/oil/generate.html
HELP;

		\Cli::write($output);
	}


	// Helper functions


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


	private function _build_migration($migration_name, $mode, $table, $args)
	{
		$migration_name = ucfirst(strtolower($migration_name));

		if ($mode == 'create_table' or $mode == 'add_fields')
		{
			// Store an aray of what fields are being added
			$fields = array();
			
			$field_str = '';

			foreach ($args as $arg)
			{
				// Parse the argument for each field in a pattern of name:type[constraint]
				preg_match('/([a-z0-9_]+):([a-z0-9_]+)(\[([0-9]+)\])?/i', $arg, $matches);

				$name = $fields[] = $matches[1];
				$type = $matches[2];
				$constraint = isset($matches[4]) ? $matches[4] : null;

				if ($type === 'string')
				{
					$type = 'varchar';
				}
				
				else if ($type === 'integer')
				{
					$type = 'int';
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

				// Shove an id field at the start
				$field_str = "\t\t\t'id' => array('type' => 'int', 'auto_increment' => true),".PHP_EOL . $field_str;

				$up = <<<UP
		\DBUtil::create_table('{$table}', array(
$field_str
		), array('id'));
UP;

				$down = <<<DOWN
		\DBUtil::drop_table('{$table}');
DOWN;
			break;

			case 'drop_table':
				$up = <<<UP
		\DBUtil::drop_table('{$table}');
UP;
				$down = '';
			break;

			default:
				$up = '';
				$down = '';
		}


		$migration = <<<MIGRATION
<?php

namespace Fuel\Migrations;

class {$migration_name} {

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

		if (glob(APPPATH . 'migrations/*_' . strtolower($migration_name) . '.php'))
		{
			throw new Exception('A migration with this name already exists.');
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
 		foreach ($actions as $key => $action)
		{
			if (substr($action, 0, 1) === '-')
			{
				unset($actions[$key]);
			}
		}

		return $actions;
	}
}

/* End of file oil/classes/generate.php */