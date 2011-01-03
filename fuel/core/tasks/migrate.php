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

namespace Fuel\Tasks;

/**
 * Migrate task
 *
 * Use this command line task to deploy and rollback changes.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Phil Sturgeon
 * @link		http://fuelphp.com/docs/general/migrations.html
 */

class Migrate {

	public static function run()
	{
		$version = \Cli::option('v', \Cli::option('version'));

		if ($version > 0)
		{
			\Migrate::version($version);
		}

		else
		{
			\Migrate::current();
		}
	}

	public static function up()
	{
		\Config::load('migration', true);
		$version = \Config::get('migration.version') + 1;

		\Migrate::version($version);
		static::_update_version($version);
	}

	public static function down()
	{
		\Config::load('migration', true);
		$version = \Config::get('migration.version') - 1;

		\Migrate::version($version);
		static::_update_version($version);
	}

	private static function _update_version($version)
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

	public static function install()
	{
		\Migrate::install();
	}

	public static function help()
	{
		echo <<<HELP
Usage:
    php oil refine migrate [--version=X]

Fuel options:
    -v, [--version]  # Migrate to a specific version

Description:
    The migrate task can run migrations. You can go up, down or by default go to the current migration marked in the config file.

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