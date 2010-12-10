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

use Fuel\Application as App;

class Refine
{
	public function run($task, $args)
	{
		$task = ucfirst(strtolower($task));

		if ( ! $file = App\Fuel::find_file('tasks', $task))
		{
			throw new Exception('Well that didnt work...');
			return;
		}

		require $file;

		$task = '\\Fuel\\Tasks\\'.$task;
		
		if ($return = call_user_func_array(array(new $task, 'run'), $args))
		{
			echo $return.PHP_EOL;
		}
	}
}

/* End of file model.php */