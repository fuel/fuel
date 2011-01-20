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

namespace Oil;

/**
 * Oil\Generate_Migration_Actions
 * Handles actions for generating migrations in Oil
 *
 * @package		Fuel
 * @subpackage	Oil
 * @category	Core
 * @author		Tom Arnfeld
 */
class Generate_Migration_Actions
{
	
	/**
	 * Each migration action should return an array with two items, 0 being up and 1 being down.
	 */
	
	// Create table
	public static function create_table_action($migration_name, $args)
	{
		$table = str_replace('create_', '', $migration_name);
		
		$fields = array();

		$field_str = '';

		foreach ($args as $arg)
		{
			// Parse the argument for each field in a pattern of name:type[constraint]
			preg_match('/([a-z0-9_]+):([a-z0-9_]+)(\[([0-9]+)\])?/i', $arg, $matches);

			$name = $fields[] = $matches[1];
			$type = $matches[2];
			$constraint = isset($matches[4]) ? $matches[4] : null;

			if ($type === 'string')
			{
				$type = 'varchar';
			}
			else if ($type === 'integer')
			{
				$type = 'int';
			}
			if (in_array($type, array('text', 'blob', 'datetime')))
			{
				$field_str .= "\t\t\t'$name' => array('type' => '$type'),".PHP_EOL;
			}
			else
			{
				if ($constraint === null)
				{
					$constraint = self::$_default_constraints[$type];
				}

				$field_str .= "\t\t\t'$name' => array('type' => '$type', 'constraint' => $constraint),".PHP_EOL;
			}
		}
		
		$field_str = "\t\t\t'id' => array('type' => 'int', 'auto_increment' => true),".PHP_EOL . $field_str;

		$up = <<<UP
		\DBUtil::create_table('{$table}', array(
$field_str
		), array('id'));
UP;

		$down = <<<DOWN
		\DBUtil::drop_table('{$table}');
DOWN;
		
		return array($up, $down);
	}
	
	// Add Fields to table
	public static function add_fields_action($migration_name, $args)
	{
		preg_match('/add_[a-z0-9_]+_to_([a-z0-9_]+)/i', $migration_name, $matches);
		$table = $matches[1];
		
		return array('', '');
	}
	
	// Remove fields from table
	public static function remove_field_action($migration_name, $args)
	{
		preg_match('/remove_([a-z0-9_])+_from_([a-z0-9_]+)/i', $migration_name, $matches);

		$remove_field = $matches[1];
		$table = $matches[2];
		
		return array('', '');
	}
	
	// Rename a table
	public static function rename_table_action($migration_name, $args)
	{
		preg_match('/rename_table_([a-z0-9_]+)+_to_([a-z0-9_]+)/i', $migration_name, $matches);
		
		$table = $matches[1];
		$args[] = $matches[2];
		
		$up = <<<UP
		\DBUtil::rename_table('{$table}', '{$args[0]}');
UP;
		$down = <<<DOWN
		\DBUtil::rename_table('{$args[0]}', '{$table}');
DOWN;
		
		return array($up, $down);
	}
	
	// Drop a table
	public static function drop_table_action($migration_name, $args)
	{
		$table = str_replace('drop_', '', $migration_name);
		
		$up = <<<UP
		\DBUtil::drop_table('{$table}');
UP;
		$down = '';
		
		return array($up, $down);
	}
	
}