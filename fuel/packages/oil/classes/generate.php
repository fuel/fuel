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
			\Cli::write("\t".'Created controller: ' . \Fuel::clean_path($filepath));
		}
	}


	public function model($args)
	{
		$singular = strtolower(array_shift($args));

		if (empty($args))
		{
			throw new Exception('No fields have been provided, the model will not know how to build the table.');
		}

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
			\Cli::write("\t".'Created model: ' . \Fuel::clean_path($filepath));
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
				\Cli::write("\t".'Created view: ' . \Fuel::clean_path($view_file));
			}
		}
	}


	public function migration($args)
	{
		
		// Get the migration name
		$migration_name = strtolower(array_shift($args));
		
		// See if the action exists
		$methods = get_class_methods(__NAMESPACE__ . '\Generate_Migration_Actions');
		
		// For empty migrations that dont have actions
		$migration = array('', '');
		
		// Loop through the actions and act on a matching action appropriately
		foreach($methods as $method_name)
		{			
			if(substr($migration_name, 0, strlen($method_name)) === $method_name)
			{
				$subjects = array(false, false);
				$matches = explode('_', str_replace($method_name . '_', '', $migration_name));
				
				if(count($matches) == 1) {
					$subjects = array(false, $matches[0]);
				}
				else if(count($matches) == 3)
				{
					$subjects = array($matches[0], $matches[2]);
				}
				else
				{
					break;
				}
				
				// We always pass in fields to a migration, so lets sort them out here.
				$fields = array();
				foreach($args as $field)
				{
					$field_array = array();
					
					$parts = explode(":", $field);
					if(count($parts) >= 2)
					{
						$field_array['name'] = array_shift($parts);
						foreach($parts as $part)
						{
							preg_match('/([a-z]+)(?:\[([0-9]+)\])?/i', $part, $part_matches);
							array_shift($part_matches);
							
							// Here you should add checks for special flags like possibly :unique or :notnull ?
							// if($part_matches == 'unique') { ... break; }
							
							$field_array['type'] = $part_matches[0];
							if(isset($part_matches[1]))
							{
								$field_array['constraint'] = $part_matches[1];
							}
							
							if($field['type'] === 'string')
							{
								$field['type'] = 'varchar';
							}
							else if($field['type'] === 'integer')
							{
								$field['type'] = 'int';
							}

							if(!in_array($field['type'], array('text', 'blob', 'datetime')))
							{
								if(!isset($field['constraint']))
								{
									$field['constraint'] = self::$_default_constraints[$type];
								}
							}
							
						}
						$fields[] = $field_array;
					}
					else
					{
						// Invalid field passed in
						continue;
					}
				}
				
				\Cli::write('Building magic migration: ' . $method_name);
				$migration = call_user_func(__NAMESPACE__ . "\Generate_Migration_Actions::{$method_name}", $subjects, $fields);
				
			}
			else
			{
				// No magic action for this migration...
			}
		}
		
		// Build the migration
		if ($filepath = static::_build_migration($migration_name, $migration[0], $migration[1]))
		{
			\Cli::write('Created migration: ' . \Fuel::clean_path($filepath), 'green');
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
  php oil g scaffold/template_subfolder <modelname> [<fieldname1>:<type1> |<fieldname2>:<type2> |..]

Note that the next two lines are equivalent: 
  php oil g scaffold <modelname> ...
  php oil g scaffold/default <modelname> ...
  
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
	
	private function _build_migration($migration_name, $up, $down)
	{
		$migration_name = ucfirst(strtolower($migration_name));

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