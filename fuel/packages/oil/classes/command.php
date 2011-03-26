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
 * Oil\Cli Class
 *
 * @package        Fuel
 * @subpackage    Oil
 * @category    Core
 * @author        Phil Sturgeon
 */
class Command
{
	public static function init($args)
	{
		// Remove flag options from the main argument list
		for ($i =0; $i < count($args); $i++)
		{
			if (strpos($args[$i], '-') === 0)
			{
				unset($args[$i]);
			}
		}

		try
		{
			if ( ! isset($args[1]))
			{
				if (\Cli::option('v', \Cli::option('version')))
				{
					\Cli::write('Fuel: ' . \Fuel::VERSION);
					return;
				}

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

					// Developers of third-party tasks may not be displaying PHP errors. Report any error and quit
					set_error_handler(function($errno, $errstr, $errfile, $errline){
						\Cli::error("Error: {$errstr} in $errfile on $errline");
						\Cli::beep();
						exit;
					});

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

					// Attempt to load PUPUnit.  If it fails, we are done.
					if (!!\class_exists('PHPUnit_Framework_TestCase'))
					{
						throw new Exception('PHPUnit does not appear to be installed.'.PHP_EOL.PHP_EOL."\tPlease visit http://phpunit.de and install.");
					}

					// CD to the root of Fuel and call up phpunit with a path to our config
					$command = 'cd '.DOCROOT.'; phpunit -c "'.COREPATH.'phpunit.xml"';

					// Respect the group option
					\Cli::option('group') and $command .= ' --group '.\Cli::option('group');

					passthru($command);
				
				break;
 
				default:

					static::help();
			}
		}

		catch (Exception $e)
		{
			\Cli::error('Error: '.$e->getMessage());
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

Description:
  The 'oil' command can be used in several ways to facilitate quick development, help with
  testing your application and for running Tasks.

Documentation:
  http://fuelphp.com/docs/packages/oil/intro.html

HELP;

    }
}

/* End of file oil/classes/cli.php */
