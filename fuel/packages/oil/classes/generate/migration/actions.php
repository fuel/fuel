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
	public static function create_table_action($migration_name)
	{
		$table = str_replace('create_', '', $migration_name);
		
		return array('', '');
	}
	
	// Add Fields to table
	public static function add_fields_action($migration_name)
	{
		preg_match('/add_[a-z0-9_]+_to_([a-z0-9_]+)/i', $migration_name, $matches);
		$table = $matches[1];
		
		return array('', '');
	}
	
	// Remove fields from table
	public static function remove_field_action($migration_name)
	{
		preg_match('/remove_([a-z0-9_])+_from_([a-z0-9_]+)/i', $migration_name, $matches);

		$remove_field = $matches[1];
		$table = $matches[2];
		
		return array('', '');
	}
	
	// Rename a table
	public static function rename_table_action($migration_name)
	{
		preg_match('/rename_table_([a-z0-9_]+)+_to_([a-z0-9_]+)/i', $migration_name, $matches);
		
		$table = $matches[1];
		$args[] = $matches[2];
		
		return array('', '');
	}
	
	// Drop a table
	public static function drop_table_action($migration_name)
	{
		$table = str_replace('drop_', '', $migration_name);
		
		return array('', '');
	}
	
}