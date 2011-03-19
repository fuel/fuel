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

abstract class Relation {

	/**
	 * @var  Model  classname of the parent model
	 */
	protected $model_from;

	/**
	 * @var  string  classname of the related model
	 */
	protected $model_to;

	/**
	 * @var  string  primary key of parent model
	 */
	protected $key_from = array('id');

	/**
	 * @var  string  foreign key in related model
	 */
	protected $key_to = array();

	/**
	 * @var  bool  whether it's a single object or multiple
	 */
	protected $singular = false;

	/**
	 * Configures the relationship
	 *
	 * @param  string  the model that initiates the relationship
	 * @param  string  name of the relationship
	 * @param  array   config values like model_to classname, key_from & key_to
	 */
	abstract public function __construct($from, $name, array $config);

	/**
	 * Should get the objects related to the given object by this relation
	 *
	 * @param   Model
	 * @return  object|array
	 */
	abstract public function get(Model $from);

	/**
	 * Should get the properties as associative array with alias => property, the table alias is
	 * given to be included with the property
	 *
	 * @param   string
	 * @return  array
	 */
	abstract public function select($table);

	/**
	 * Returns tables to join and fields to select with optional additional settings like order/where
	 *
	 * @param   string  alias for the table
	 * @return  array
	 */
	abstract public function join($alias);

	/**
	 * Allow outside access to protected properties
	 *
	 * @param  $property
	 */
	public function __get($property)
	{
	if (strncomp($property, '_', 1) != 0 or ! property_exists($this, $property))
	{
		throw new Exception('Invalid relation property.');
	}

		return $this->{$property};
	}
}

/* End of file relation.php */