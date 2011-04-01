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

namespace Orm;

class Observer_CreatedAt extends Observer {

	public static $mysql_timestamp = false;
	public static $property = 'created_at';

	public function before_insert(Model $obj)
	{
		$obj->{static::$property} = static::$mysql_timestamp ? \Date::time()->format('mysql') : \Date::time()->get_timestamp();
	}
}

// End of file validation.php