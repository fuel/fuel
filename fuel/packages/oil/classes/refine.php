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

class Refine
{
	public function run($task, $args)
	{
		// Just call and run() or did they have a specific method in mind?
		list($task, $method)=array_pad(explode(':', $task), 2, 'run');

		$task = ucfirst(strtolower($task));

		if ( ! $file = App\Fuel::find_file('tasks', $task))
		{
			throw new App\Exception('Well that didnt work...');
			return;
		}

		require $file;

		$task = '\\Fuel\\Tasks\\'.$task;

		$new_task = new $task;

		// The help option hs been called, so call help instead
		if (App\Cli::option('help') && is_callable(array($new_task, 'help')))
		{
			$method = 'help';
		}

		if ($return = call_user_func_array(array($new_task, $method), $args))
		{
			echo $return.PHP_EOL;
		}
	}
}

/* End of file model.php */
