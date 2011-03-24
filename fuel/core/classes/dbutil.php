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
 * DBUtil Class
 *
 * @package		Fuel
 * @category	Core
 * @author		Dan Horrigan
 */
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
		return DB::query('CREATE DATABASE '.DB::quote_identifier($database), \DB::UPDATE)->execute();
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
		return DB::query('DROP DATABASE '.DB::quote_identifier($database), \DB::DELETE)->execute();
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
		return DB::query('DROP TABLE IF EXISTS '.DB::quote_identifier($table), \DB::DELETE)->execute();
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
		return DB::query('RENAME TABLE '.DB::quote_identifier($table).' TO '.DB::quote_identifier($new_table_name),DB::UPDATE)->execute();
	}

	public static function create_table($table, $fields, $primary_keys = array(), $if_not_exists = true)
	{
		$sql = 'CREATE TABLE';

		$sql .= $if_not_exists ? ' IF NOT EXISTS ' : ' ';

		$sql .= DB::quote_identifier($table).' (';
		$sql .= static::process_fields($fields);
		if ( ! empty($primary_keys))
		{
			$key_name = DB::quote_identifier(implode('_', $primary_keys));
			$primary_keys = DB::quote_identifier($primary_keys);
			$sql .= ",\n\tPRIMARY KEY ".$key_name." (" . implode(', ', $primary_keys) . ")";
		}
		$sql .= "\n);";

		return DB::query($sql, DB::UPDATE)->execute();
	}

	protected static function process_fields($fields)
	{
		$sql_fields = array();

		foreach ($fields as $field => $attr)
		{
			$sql = "\n\t";
			$attr = array_change_key_case($attr, CASE_UPPER);

			$sql .= DB::quote_identifier($field);
			$sql .= array_key_exists('NAME', $attr) ? ' '.DB::quote_identifier($attr['NAME']).' ' : '';
			$sql .= array_key_exists('TYPE', $attr) ? ' '.$attr['TYPE'] : '';
			$sql .= array_key_exists('CONSTRAINT', $attr) ? '('.$attr['CONSTRAINT'].')' : '';

			if (array_key_exists('UNSIGNED', $attr) && $attr['UNSIGNED'] === true)
			{
				$sql .= ' UNSIGNED';
			}

			$sql .= array_key_exists('DEFAULT', $attr) ? ' DEFAULT '. (($attr['DEFAULT'] instanceof \Database_Expression) ? $attr['DEFAULT']  : DB::escape($attr['DEFAULT'])) : '';
			$sql .= array_key_exists('NULL', $attr) ? (($attr['NULL'] === true) ? ' NULL' : ' NOT NULL') : '';

			if (array_key_exists('AUTO_INCREMENT', $attr) && $attr['AUTO_INCREMENT'] === true)
			{
				$sql .= ' AUTO_INCREMENT';
			}
			$sql_fields[] = $sql;
		}

		return \implode(',', $sql_fields);
	}

	/**
	 * Truncates a table.
	 *
	 * @throws    Fuel\Database_Exception
	 * @param     string    $table    the table name
	 * @return    int       the number of affected rows
	 */
	public static function truncate_table($table)
	{
		return DB::query('TRUNCATE TABLE '.DB::quote_identifier($table), \DB::DELETE)->execute();
	}
	
	/**
	 * Analyzes a table.
	 *
	 * @param     string    $table    the table name
	 * @return    bool      whether the table is OK
	 */
	public static function analyze_table($table)
	{
		return static::table_maintenance('ANALYZE TAB:E', $table);
	}
	
	/**
	 * Checks a table.
	 *
	 * @param     string    $table    the table name
	 * @return    bool      whether the table is OK
	 */
	public static function check_table($table)
	{
		return static::table_maintenance('CHECK TABLE', $table);
	}
	
	/**
	 * Optimizes a table.
	 *
	 * @param     string    $table    the table name
	 * @return    bool      whether the table has been optimized
	 */
	public static function optimize_table($table)
	{
		return static::table_maintenance('OPTIMIZE TABLE', $table);
	}
	
	/**
	 * Repairs a table.
	 *
	 * @param     string    $table    the table name
	 * @return    bool      whether the table has been repaired
	 */
	public static function repair_table($table)
	{
		return static::table_maintenance('REPAIR TABLE', $table);
	}
	
	/*
	 * Executes table maintenance. Will throw Fuel_Exception when the operation is not supported.
	 *
	 * @throws	Fuel_Exception
	 * @param     string    $table    the table name
	 * @return    bool      whether the operation has succeeded
	 */
	protected static function table_maintenance($operation, $table)
	{
		$result = \DB::query($operation.' '.\DB::quote_identifier($table), \DB::SELECT)->execute();
		$type = $result->get('Msg_type');
		$message = $result->get('Msg_text');
		$table = $result->get('Table');
		if($type === 'status' and in_array(strtolower($message), array('ok','table is already up to date')))
		{
			return true;
		}
		
		if($type === 'error')
		{
			\Log::error('Table: '.$table.', Operation: '.$operation.', Message: '.$result->get('Msg_text'), 'DBUtil::table_maintenance');
		}
		else
		{
			\Log::write(ucfirst($type), 'Table: '.$table.', Operation: '.$operation.', Message: '.$result->get('Msg_text'), 'DBUtil::table_maintenance');
		}
		return false;
	}

}

/* End of file dbutil.php */
