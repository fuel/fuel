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
 * Oil\Cli Class
 *
 * @package		Fuel
 * @subpackage	Oil
 * @category	Core
 * @author		Phil Sturgeon
 */
class Cli
{
	public static function init($args)
	{
		try
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

					$action = isset($args[2]) ? $args[2]: 'help';
					
					$subfolder = 'default';
					if (is_int(strpos($action, 'scaffold/')))
					{
						$subfolder = str_replace('scaffold/', '', $action);
						$action = 'scaffold';
					}
					
					switch ($action)
					{
						case 'controller':
						case 'model':
						case 'views':
						case 'migration':
							call_user_func('Oil\Generate::'.$action, array_slice($args, 3));
						break;

						case 'scaffold':
							call_user_func('Oil\Scaffold::generate', array_slice($args, 3), $subfolder);
						break;

						default:
							Generate::help();
					}

				break;

				case 'c':
				case 'console':
					new Console;

				case 'r':
				case 'refine':
					$task = isset($args[2]) ? $args[2] : null;

					call_user_func('Oil\Refine::run', $task, array_slice($args, 3));
				break;

				case 'p':
				case 'package':

					$action = isset($args[2]) ? $args[2]: 'help';
					
					switch ($action)
					{
						case 'install':
						case 'uninstall':
							call_user_func_array('Oil\Package::'.$action, array_slice($args, 3));
						break;

						default:
							Package::help();
					}

				break;

				case 't':
				case 'test':
					\Fuel::add_package('octane');
					
					$action = isset($args[2]) ? $args[2]: '--help';
					
					switch ($action)
					{
						case '--help':
							\Fuel\Octane\Tests::help();
						break;
						
						default:
							call_user_func('\\Fuel\\Octane\\Tests::run_'.$action, array_slice($args, 3));
					}

				break;
 
				case '-v':
				case '--version':
					\Cli::write('Fuel: ' . \Fuel::VERSION);
				break;

				default:
					static::help();
			}
		}

		catch (Exception $e)
		{
			\Cli::write(\Cli::color('Error: ' . $e->getMessage(), 'light_red'));
			\Cli::beep();
		}
	}

	public static function help()
	{
		echo <<<HELP
   
Usage:
  php oil [console|generate|help|test|package]

Runtime options:
  -f, [--force]    # Overwrite files that already exist
  -s, [--skip]     # Skip files that already exist
  -q, [--quiet]    # Supress status output

Fuel options:
  -v, [--version]  # Show Fuel version number and quit

Description:
  The 'oil' command can be used in serveral ways to facilitate quick development, help with
  testing your application and for running Tasks.

Documentation:
  http://fuelphp.com/docs/packages/oil/intro.html

HELP;

	}
}

/* End of file oil/classes/cli.php */