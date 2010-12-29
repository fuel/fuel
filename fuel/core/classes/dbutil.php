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

namespace Fuel\Core;

use Fuel\App as App;

class DBUtil {

	/**
	 * Creates a database.  Will throw a Database_Exception if it cannot.
	 *
	 * @throws	Fuel\Database_Exception
	 * @param	string	$database	the database name
	 * @return	int		the number of affected rows
	 */
	public static function create_database($database)
	{
		return App\DB::query('CREATE DATABASE '.App\DB::quote_identifier($database), App\Database::UPDATE)->execute();
	}

	/**
	 * Drops a database.  Will throw a Database_Exception if it cannot.
	 *
	 * @throws	Fuel\Database_Exception
	 * @param	string	$database	the database name
	 * @return	int		the number of affected rows
	 */
	public static function drop_database($database)
	{
		return App\DB::query('DROP DATABASE '.App\DB::quote_identifier($database), App\Database::DELETE)->execute();
	}

	/**
	 * Creates a table.  Will throw a Database_Exception if it cannot.
	 *
	 * @throws	Fuel\Database_Exception
	 * @param	string	$table	the table name
	 * @return	int		the number of affected rows
	 */
	public static function drop_table($table)
	{
		return App\DB::query('DROP TABLE IF EXISTS '.App\DB::escape($table), App\Database::DELETE);
	}

	/**
	 * Renames a table.  Will throw a Database_Exception if it cannot.
	 *
	 * @throws	Fuel\Database_Exception
	 * @param	string	$table			the old table name
	 * @param	string	$new_table_name	the new table name
	 * @return	int		the number of affected
	 */
	public static function rename_table($table, $new_table_name)
	{
		return App\DB::query('DROP TABLE IF EXISTS '.App\DB::escape($table), App\Database::UPDATE);
	}

	public static function create_table($table, $fields, $primary_keys = array(), $if_not_exists = true)
	{
		$sql = 'CREATE TABLE';

		$sql .= $if_not_exists ? ' IF NOT EXISTS ' : ' ';

		$sql .= App\DB::quote_identifier($table).' (';
		$sql .= static::process_fields($fields);
		if ( ! empty($primary_keys))
		{
			$key_name = App\DB::quote_identifier(implode('_', $primary_keys));
			$primary_keys = App\DB::quote_identifier($primary_keys);
			$sql .= ",\n\tPRIMARY KEY ".$key_name." (" . implode(', ', $primary_keys) . ")";
		}
		$sql .= "\n);";

		return App\DB::query($sql, App\Database::UPDATE)->execute();
	}

	protected static function process_fields($fields)
	{
		$sql_fields = array();

		foreach ($fields as $field => $attr)
		{
			$sql = "\n\t";
			$attr = array_change_key_case($attr, CASE_UPPER);

			$sql .= App\DB::quote_identifier($field);
			$sql .= array_key_exists('NAME', $attr) ? ' '.App\DB::quote_identifier($attr['NAME']).' ' : '';
			$sql .= array_key_exists('TYPE', $attr) ? ' '.$attr['TYPE'] : '';
			$sql .= array_key_exists('CONSTRAINT', $attr) ? '('.$attr['CONSTRAINT'].')' : '';

			if (array_key_exists('UNSIGNED', $attr) && $attr['UNSIGNED'] === true)
			{
				$sql .= ' UNSIGNED';
			}

			$sql .= array_key_exists('DEFAULT', $attr) ? ' DEFAULT '.App\DB::escape($attr['DEFAULT']) : '';
			$sql .= array_key_exists('NULL', $attr) ? (($attr['NULL'] === true) ? ' NULL' : ' NOT NULL') : '';

			if (array_key_exists('AUTO_INCREMENT', $attr) && $attr['AUTO_INCREMENT'] === true)
			{
				$sql .= ' AUTO_INCREMENT';
			}
			$sql_fields[] = $sql;
		}

		return \implode(',', $sql_fields);
	}
}

/* End of file dbutil.php */
