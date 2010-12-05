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

namespace Cli;

use Fuel\Application as App;

class Cli
{
	public function init($args)
	{
		if ( ! isset($args[1]))
		{
			static::help();
			return;
		}

		switch ($args[1])
		{
			case 'g':
			case 'generate':

				switch ($args[2])
				{
					case 'controller':
					case 'model':
					case 'view':
					case 'views':
					case 'model':

						call_user_func('static::'.$args[2], array_slice($args, 3));

					break;

					default:
						Generate::help();
				}
			break;

			case '-v':
			case '--version':

				echo 'Fuel: ' . App\Fuel::VERSION;
				return;

			default:
				static::help();
		}
	}

	public function controller($args)
	{
		App\Debug::dump($args);
	}

	public function model($args)
	{
		$singular = strtolower(array_shift($args));

		$table = App\Inflector::pluralize($singular);

		$filepath = APPPATH . 'classes/model/' . $singular .'.php';

		$class_name = 'Model_' . ucfirst($singular);

		$model = <<<MODEL
<?php

namespace Fuel\Application;

use ActiveRecord;

class {$class_name} extends ActiveRecord\Model {

/* End of file $singular.php */
MODEL;

		if (!$handle = fopen($filepath, 'w+'))
		{
			throw new Exception('Cannot open file: '. $filepath);
		}

		// Write $somecontent to our opened file.
		if (fwrite($handle, $model) === FALSE)
		{
			throw new Exception('Cannot write to file: '. $filepath);
		}

		echo "Created model $singular";

		fclose($handle);
	}

	public function help()
	{
		echo <<<HELP
Usage:
  php fuel.php generate [controller|model|migration|view|views] [options]

Runtime options:
  -f, [--force]    # Overwrite files that already exist
  -s, [--skip]     # Skip files that already exist
  -p, [--pretend]  # Run but do not make any changes
  -q, [--quiet]    # Supress status output

Fuel options:
  -v, [--version]  # Show Fuel version number and quit

Description:
    The 'fuel' command can be used to generate MVC components, database migrations
    and run specific tasks.

Examples:
    php fuel.php g controller <controllername> [<action1> |<action2> |..]
    php fuel.php g model <modelname> [<fieldname1>:<type1> |<fieldname2>:<type2> |..]
HELP;

	}
}


/* End of file model.php */