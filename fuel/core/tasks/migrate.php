<?php

namespace Fuel\Tasks;

use Fuel\App as App;

class Migrate {

	public function run()
	{
		$version = App\Cli::option('v', App\Cli::option('version'));

		if ($version > 0)
		{
			App\Migrate::version($version);
		}

		else
		{
			App\Migrate::current();
		}
	}

	public function up()
	{
		App\Migrate::up();
	}

	public function down()
	{
		App\Migrate::down();
	}

	public function help()
	{
		echo <<<HELP
Usage:
    php oil refine migrate [--version=X]

Fuel options:
    -v, [--version]  # Migrate to a specific version

Description:
    The migrate task can run migrations. You can go up, down or by default go to the current migration marked in the ocnfig file.

Examples:
    php oil r migrate
    php oil r migrate:up
    php oil r migrate:down
    php oil r migrate --version=10

HELP;

	}
}

/* End of file tasks/migrate.php */