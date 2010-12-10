<?php

namespace Fuel\Tasks;

use Fuel\Application as App;

class Migrate {

	public function run($direction = null)
	{
		// By default, just upgrade to the current version
		if ($direction === null)
		{
			App\Migrate::current();
		}

		else
		{
			// Find out what they want to do with it
			switch ($direction)
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
					App\Migrate::current();
				break;

				case '-v':
				case '--version':

					if (empty($args[1]))
					{
						throw new App\Cli\Exception('');
					}

					App\Migrate::version($args[1]);
				break;
			}
		}
	}
}

/* End of file tasks/migrate.php */