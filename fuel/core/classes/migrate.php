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

namespace Fuel\Core;

/**
 * Migrate Class
 *
 * @package		Fuel
 * @category	Migrations
 * @author		Phil Sturgeon
 * @link		http://fuelphp.com/docs/classes/migrate.html
 */
class Migrate
{
	public static $version = 0;

	protected static $prefix = '\\Fuel\Migrations\\';

	public static function _init()
	{
		logger(Fuel::L_DEBUG, 'Migrate class initialized');

		\Config::load('migrations', true);

		\DBUtil::create_table('migration', array(
			'current' => array('type' => 'int', 'constraint' => 11, 'null' => false, 'default' => 0)
		));

		// Check if there is a version
		$current = \DB::select('current')->from('migration')->execute()->get('current');

		// Not set, so we are on 0
		if ($current === null)
		{
			\DB::insert('migration')->set(array('current' => '0'))->execute();
		}

		else
		{
			static::$version = (int) $current;
		}
	}

	/**
	 * Set's the schema to the latest migration
	 *
	 * @access	public
	 * @return	mixed	true if already latest, false if failed, int if upgraded
	 */
	public static function latest()
	{
		if ( ! $migrations = static::find_migrations())
		{
			throw new Fuel_Exception('no_migrations_found');
			return false;
		}

		$last_migration = basename(end($migrations));

		// Calculate the last migration step from existing migration
		// filenames and procceed to the standard version migration
		$last_version = intval(substr($last_migration, 0, 3));
		return static::version($last_version);
	}

	// --------------------------------------------------------------------

	/**
	 * Set's the schema to the migration version set in config
	 *
	 * @access	public
	 * @return	mixed	true if already current, false if failed, int if upgraded
	 */
	public static function current()
	{
		return static::version(\Config::get('migrations.version'));
	}

	// --------------------------------------------------------------------

	/**
	 * Migrate to a schema version
	 *
	 * Calls each migration step required to get to the schema version of
	 * choice
	 *
	 * @access	public
	 * @param $version integer	Target schema version
	 * @return	mixed	true if already latest, false if failed, int if upgraded
	 */
	public static function version($version)
	{
		if (static::$version === $version)
		{
			return false;
		}

		$start = static::$version;
		$stop = $version;

		if ($version > static::$version)
		{
			// Moving Up
			++$start;
			++$stop;
			$step = 1;
		}

		else
		{
			// Moving Down
			$step = -1;
		}

		$method = $step === 1 ? 'up' : 'down';
		$migrations = array();

		// We now prepare to actually DO the migrations
		// But first let's make sure that everything is the way it should be
		for ($i = $start; $i != $stop; $i += $step)
		{
			$f = glob(sprintf(\Config::get('migrations.path') . '%03d_*.php', $i));

			// Only one migration per step is permitted
			if (count($f) > 1)
			{
				throw new Fuel_Exception('multiple_migrations_version');
				return false;
			}

			// Migration step not found
			if (count($f) == 0)
			{
				// If trying to migrate up to a version greater than the last
				// existing one, migrate to the last one.
				if ($step == 1) break;

				// If trying to migrate down but we're missing a step,
				// something must definitely be wrong.
				throw new Fuel_Exception('migration_not_found');
				return false;
			}

			$file = basename($f[0]);
			$name = basename($f[0], '.php');

			// Filename validations
			if (preg_match('/^\d{3}_(\w+)$/', $name, $match))
			{
				$match[1] = strtolower($match[1]);

				// Cannot repeat a migration at different steps
				if (in_array($match[1], $migrations))
				{
					throw new Fuel_Exception('multiple_migrations_name');
					return false;
				}

				include $f[0];
				$class = static::$prefix . ucfirst($match[1]);

				if ( ! class_exists($class))
				{
					throw new Fuel_Exception('migration_class_doesnt_exist');
					return false;
				}

				if ( ! is_callable(array($class, 'up')) || !is_callable(array($class, 'down')))
				{
					throw new Fuel_Exception('wrong_migration_interface');
					return false;
				}

				$migrations[] = $match[1];
			}
			else
			{
				throw new Fuel_Exception('invalid_migration_filename');
				return false;
			}
		}

		$version = $i + ($step == 1 ? -1 : 0);

		// If there is nothing to do, bitch and quit
		if ($migrations === array())
		{
			return false;
		}

		// Loop through the migrations
		foreach ($migrations as $migration)
		{
			logger(Fuel::L_INFO, 'Migrating to: '.static::$version + $step);

			$class = static::$prefix . ucfirst($migration);
			call_user_func(array(new $class, $method));

			static::$version += $step;
			static::_update_schema_version(static::$version - $step, static::$version);
		}

		logger(Fuel::L_INFO, 'Migrated to ' . static::$version.' successfully.');

		return static::$version;
	}

	// --------------------------------------------------------------------

	/**
	 * Set's the schema to the latest migration
	 *
	 * @access	public
	 * @return	mixed	true if already latest, false if failed, int if upgraded
	 */

	protected static function find_migrations()
	{
		// Load all *_*.php files in the migrations path
		$files = glob(\Config::get('migrations.path') . '*_*.php');
		$file_count = count($files);

		for ($i = 0; $i < $file_count; $i++)
		{
			// Mark wrongly formatted files as false for later filtering
			$name = basename($files[$i], '.php');
			if ( ! preg_match('/^\d{3}_(\w+)$/', $name))
			{
				$files[$i] = false;
			}
		}

		sort($files);

		return $files;
	}

	// --------------------------------------------------------------------

	/**
	 * Stores the current schema version
	 *
	 * @access	private
	 * @param $schema_version integer	Schema version reached
	 * @return	void					Outputs a report of the migration
	 */
	private static function _update_schema_version($old_version, $version)
	{
		\DB::update('migration')->set(array('current' => (int) $version))->where('current', '=', (int) $old_version)->execute();
	}
}
