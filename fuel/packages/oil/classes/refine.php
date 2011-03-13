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
		// Make sure something is set
		if ($task === null OR $task === 'help')
		{
			static::help();
			return;
		}

		// Just call and run() or did they have a specific method in mind?
		list($task, $method)=array_pad(explode(':', $task), 2, 'run');

		$task = ucfirst(strtolower($task));

		// Find the task
		if ( ! $file = \Fuel::find_file('tasks', $task))
		{
			$files = \Fuel::list_files('tasks');
			$possibilities = array();
			foreach($files as $file)
			{
				$possible_task = pathinfo($file, \PATHINFO_FILENAME);
				$difference = levenshtein($possible_task, $task);
				$possibilities[$difference] = $possible_task;
			}

			ksort($possibilities);
			
			if ($possibilities and current($possibilities) <= 5)
			{
				throw new Exception(sprintf('Task "%s" does not exist. Did you mean "%s"?', strtolower($task), current($possibilities)));
			}
			else
			{
				throw new Exception(sprintf('Task "%s" does not exist.', strtolower($task)));
			}
			
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
		$output = <<<HELP

Usage:
  php oil [r|refine] <taskname>

Description:
    Tasks are classes that can be run through the the command line or set up as a cron job.

Examples:
    php oil refine robots [<message>]
    php oil refine robots:protect

Documentation:
	http://fuelphp.com/docs/packages/oil/refine.html
HELP;
		\Cli::write($output);

	}
}

/* End of file oil/classes/refine.php */