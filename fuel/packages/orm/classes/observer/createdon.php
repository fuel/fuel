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

class Observer_CreatedOn extends Observer {

	public function before_insert(Model $obj)
	{
		$obj->created_on = \Date::time()->get_timestamp();
	}
}

// End of file validation.php