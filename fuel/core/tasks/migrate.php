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
		App\Config::load('migration', true);
		$version = App\Config::get('migration.version') + 1;

		App\Migrate::version($version);
		static::_update_version($version);
	}

	public function down()
	{
		App\Config::load('migration', true);
		$version = App\Config::get('migration.version') - 1;

		App\Migrate::version($version);
		static::_update_version($version);
	}

	private function _update_version($version)
	{
		$contents = '';
		$path = '';
		if (file_exists($path = APPPATH.'config'.DS.'migration.php'))
		{
			$contents = file_get_contents($path);
		}
		elseif (file_exists($path = COREPATH.'config'.DS.'migration.php'))
		{
			$contents = file_get_contents($path );
		}

		$contents = preg_replace("#('version'[ \t]+=>)[ \t]+([0-9]+),#i", "$1 $version,", $contents);

		file_put_contents($path, $contents);
	}

	public function install()
	{
		App\Migrate::install();
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
    php oil r migrate:install
    php oil r migrate:up
    php oil r migrate:down
    php oil r migrate --version=10

HELP;

	}
}

/* End of file tasks/migrate.php */