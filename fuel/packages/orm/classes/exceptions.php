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

/**
 * Base ORM Exception
 */
class Exception extends \Fuel_Exception {}

/**
 * Record Not Found Exception
 */
class RecordNotFound extends Exception {}

/**
 * Undefined Property Exception
 */
class UndefinedProperty extends Exception {}

/**
 * Undefined Relation Exception
 */
class UndefinedRelation extends Exception {}

/**
 * Invalid Observer Exception
 */
class InvalidObserver extends Exception {}

/**
 * Frozen Object Exception
 */
class FrozenObject extends Exception {}