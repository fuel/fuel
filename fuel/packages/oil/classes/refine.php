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
 * Oil\Refine Class
 *
 * @package		Fuel
 * @subpackage	Oil
 * @category	Core
 * @author		Phil Sturgeon
 */
class Refine
{
	public static function run($task, $args)
	{
		// Just call and run() or did they have a specific method in mind?
		list($task, $method)=array_pad(explode(':', $task), 2, 'run');

		$task = ucfirst(strtolower($task));

		if ( ! $file = \Fuel::find_file('tasks', $task))
		{
			throw new \Exception('Well that didnt work...');
			return;
		}

		require $file;

		$task = '\\Fuel\\Tasks\\'.$task;

		$new_task = new $task;

		// The help option hs been called, so call help instead
		if (\Cli::option('help') && is_callable(array($new_task, 'help')))
		{
			$method = 'help';
		}

		if ($return = call_user_func_array(array($new_task, $method), $args))
		{
			\Cli::write($return);
		}
	}

	public static function help()
	{
		echo <<<HELP
   
Usage:
  php oil refine <taskname>

Description:
    Tasks are classes that can be run through the the command line or set up as a cron job.

Examples:
    php oil refine robots [<message>]
    php oil refine robots:protect

HELP;

	}
}

/* End of file refine.php */