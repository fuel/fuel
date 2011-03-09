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
	 * Each migration action should return an array with two items, 0 being the up and 1 the being down.
	 */
	
	// create_{tablename}
	public static function create($subjects, $fields)
	{
		$field_str = '';
		
		foreach($fields as $field)
		{
			$name = array_shift($field);
			
			$field_opts = array();
			foreach($field as $option => $val)
			{
				if($val === true)
				{
					$field_opts[] = "'$option' => true";
				}
				else
				{
					if(is_int($val))
					{
						$field_opts[] = "'$option' => $val";
					}
					else
					{
						$field_opts[] = "'$option' => '$val'";
					}
				}
			}
			$field_opts = implode(', ', $field_opts);
			
			$field_str .= "\t\t\t'$name' => array({$field_opts}),".PHP_EOL;			
		}
		
		// ID Field
 		$field_str = "\t\t\t'id' => array('constraint' => 11, 'type' => 'int', 'auto_increment' => true),".PHP_EOL . $field_str;

		$up = <<<UP
		\DBUtil::create_table('{$subjects[1]}', array(
$field_str
		), array('id'));
UP;

		$down = <<<DOWN
		\DBUtil::drop_table('{$subjects[1]}');
DOWN;
		
		return array($up, $down);
	}
	
	// add_{thing}_to_{tablename}
	public static function add($subjects, $fields)
	{
		return array("\t\t\t// Not yet implemented this migration action", "\t\t\t// Not yet implemented this migration action");
	}
	
	// rename_field_{fieldname}_to_{newfieldname}
	public static function rename_field($subjects, $fields)
	{
		return array("\t\t\t// Not yet implemented this migration action", "\t\t\t// Not yet implemented this migration action");
	}
	
	// rename_table_{tablename}_to_{newtablename}
	public static function rename_table($subjects, $fields)
	{
		
		$up = <<<UP
		\DBUtil::rename_table('{$subjects[0]}', '{$subjects[1]}');
UP;
		$down = <<<DOWN
		\DBUtil::rename_table('{$subjects[1]}', '{$subjects[0]}');
DOWN;
		
		return array($up, $down);
	}
	
	// drop_{tablename}
	public static function drop($subjects, $fields)
	{	
		$up = <<<UP
		\DBUtil::drop_table('{$subjects[1]}');
UP;

		// TODO Create down by looking at the table and building a create

		return array($up, '');
	}
	
}