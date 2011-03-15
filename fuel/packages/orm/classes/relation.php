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

interface Relation {

	/**
	 * Configures the relationship
	 *
	 * @param	Model	the model that initiates the relationship
	 * @param	array	config values like model_to classname, key_from & key_to
	 */
	public function __construct(Model $from, $name, array $config);

	/**
	 * Should get the objects related to the given object by this relation
	 *
	 * @param	Model
	 * @return	object|array
	 */
	public function get(Model $from);

	/**
	 * Should get the properties as associative array with alias => property, the table alias is
	 * given to be included with the property
	 *
	 * @param	string
	 * @return	array
	 */
	public function select($table);

	/**
	 * Returns tables to join and fields to select with optional additional settings like order/where
	 *
	 * @param	string	alias for the table
	 * @return	array
	 */
	public function join($alias);
}

/* End of file relation.php */