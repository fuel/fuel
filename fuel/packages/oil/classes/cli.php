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
					case 'migration':

						call_user_func('Oil\Generate::'.$args[2], array_slice($args, 3));

					break;

					case 'scaffold':
						call_user_func('Oil\Scaffold::generate', array_slice($args, 3));
					break;

					default:
						Generate::help();
				}
			break;

			case 'c':
			case 'console':
				return new Console;

			case 'r':
			case 'refine':
				call_user_func('Oil\Refine::run', $args[2], array_slice($args, 3));
			break;

			case 'install':
			case 'uninstall':
				call_user_func('Oil\Package::'.$args[1], $args[2]);
			break;

			case '-v':
			case '--version':

				echo 'Fuel: ' . App\Fuel::VERSION;
				return;

			case 'test':
				App\Fuel::add_package('octane');
				call_user_func('\\Fuel\\Octane\\Tests::run_'.$args[2], array_slice($args, 3));
				break;
			default:
				static::help();
		}
		echo "Complete";
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
    php oil g controller <controllername> [<action1> |<action2> |..]
    php oil g model <modelname> [<fieldname1>:<type1> |<fieldname2>:<type2> |..]
HELP;

	}
}


/* End of file model.php */