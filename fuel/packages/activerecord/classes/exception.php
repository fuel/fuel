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

namespace ActiveRecord;

use Fuel;

class Exception extends Fuel\Core\Exception {
	const RecordNotFound = 0;
	const AttributeNotFound = 1;
	const UnexpectedClass = 2;
	const ObjectFrozen = 3;
	const HasManyThroughCantAssociateNewRecords = 4;
	const MethodOrAssocationNotFound = 5;
}


/* End of file exception.php */