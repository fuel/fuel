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
 * @package        Fuel
 * @subpackage    Oil
 * @category    Core
 * @author        Phil Sturgeon
 */
class Command
{
    public static function init($args)
    {
        if (\Cli::option('v', \Cli::option('version')))
        {
            \Cli::write('Fuel: ' . \Fuel::VERSION);
            exit;
        }


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
            \Cli::write('Error: ' . $e->getMessage(), 'light_red');
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