<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package     Fuel
 * @version     1.0
 * @author      Dan Horrigan <http://dhorrigan.com>
 * @license     MIT License
 * @copyright   2010 - 2011 Fuel Development Team
 */


namespace ActiveRecord;

use Fuel;

class Exception extends \Fuel_Exception {
    const RecordNotFound = 0;
    const AttributeNotFound = 1;
    const UnexpectedClass = 2;
    const ObjectFrozen = 3;
    const HasManyThroughCantAssociateNewRecords = 4;
    const MethodOrAssocationNotFound = 5;
}


/* End of file exception.php */