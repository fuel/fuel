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
		// -v or --version
		$version = \Cli::option('v', \Cli::option('version'));

		$run = false;

		// Spoecific version
		if ($version > 0)
		{
			if (\Migrate::version($version) === false)
			{
				throw new \Oil\Exception('Already on migration: ' . $version .'.');
			}

			else
			{
				static::_update_version($result);
				\Cli::write('Migrated to version: ' . $version .'.', 'green');
			}
		}

		// Just go to the latest
		else
		{
			if (($result = \Migrate::latest()) === false)
			{
				throw new \Oil\Exception('Already on latest migration.');
			}

			else
			{
				static::_update_version($result);
				\Cli::write('Migrated to latest version: ' . $result .'.', 'green');
			}
		}
		
	}

	public static function up()
	{
		\Config::load('migrate', true);
		$version = \Config::get('migrate.version') + 1;

		if (\Migrate::version($version))
		{
			static::_update_version($version);
			\Cli::write('Migrated to version: ' . $version .'.', 'green');
		}
	}

	public static function down()
	{
		\Config::load('migrate', true);
		$version = \Config::get('migrate.version') - 1;

		if (\Migrate::version($version))
		{
			static::_update_version($version);
			\Cli::write('Migrated to version: ' . $version .'.', 'green');
		}
	}

	private static function _update_version($version)
	{
		$contents = '';
		$path = '';
		if (file_exists($path = APPPATH.'config'.DS.'migrate.php'))
		{
			$contents = file_get_contents($path);
		}
		elseif (file_exists($core_path = COREPATH.'config'.DS.'migrate.php'))
		{
			$contents = file_get_contents($core_path);
		}

		$contents = preg_replace("#('version'[ \t]+=>)[ \t]+([0-9]+),#i", "$1 $version,", $contents);

		file_put_contents($path, $contents);
	}

	public static function current()
	{
		\Migrate::current();
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
    php oil r migrate:current
    php oil r migrate:up
    php oil r migrate:down
    php oil r migrate --version=10

HELP;

	}
}

/* End of file tasks/migrate.php */