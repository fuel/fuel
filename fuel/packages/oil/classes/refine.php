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
use Fuel\Application\DB;
use Fuel\Application\Database;

class Refine
{
	public function migrate($args)
	{
		// By default, just upgrade to the current version
		if ( ! isset($args[0]))
		{
			App\Migrate::current();
		}

		else
		{
			// Find out what they want to do with it
			switch ($args[0])
			{
				case '-u':
				case '--up':
					App\Migrate::up();
				break;

				case '-d':
				case '--down':
					App\Migrate::down();
				break;

				case '-c':
				case '--current':
					App\Migrate::down();
				break;

				case '-v':
				case '--version':

					if (empty($args[1]))
					{

					}

					App\Migrate::version($args[1]);
				break;
			}
		}
	}
		
}

/* End of file model.php */