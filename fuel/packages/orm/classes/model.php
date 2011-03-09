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
use \Inflector;

class Model {

	/* ---------------------------------------------------------------------------
	 * Static usage
	 * --------------------------------------------------------------------------- */

	/**
	 * @var	string	table name to overwrite assumption
	 */
	// protected static $_table_name;

	/**
	 * @var	string	relationships
	 */
	// protected static $_relations;

	/**
	 * @var	array	name or names of the primary keys
	 */
	protected static $_primary_key = array('id');

	/**
	 * @var	array	cached tables
	 */
	protected static $_table_names_cached = array();

	/**
	 * @var	array	cached properties
	 */
	protected static $_properties_cached = array();

	/**
	 * @var	array	array of fetched objects
	 */
	protected static $_cached_objects = array();

	public static function factory($data, $new = true)
	{
		return new static($data, $new);
	}

	/**
	 * First time the class is called staticly
	 *
	 * @return string
	 */
	public static function _init()
	{
		// Define the properties we'll be working with
		static::properties();
	}

	/**
	 * Get the table name for this class
	 *
	 * @return string
	 */
	public static function table()
	{
		$class = get_called_class();

		// Table name set in Model
		if (property_exists($class, '_table_name'))
		{
			return static::$_table_name;
		}

		// Table name unknown
		if ( ! array_key_exists($class, static::$_table_names_cached))
		{
			static::$_table_names_cached[$class] = Inflector::tableize($class);
		}

		return static::$_table_names_cached[$class];
	}

	/**
	 * Get the primary key(s) of this class
	 *
	 * @return array
	 */
	protected static function primary_key()
	{
		return static::$_primary_key;
	}

	/**
	 * Implode the primary keys within the data into a string
	 *
	 * @param	array
	 * @return	string
	 */
	protected static function implode_pk($data)
	{
		$pk = '';
		foreach(static::$_primary_key as $p)
		{
			$pk .= '['.(is_object($data) ? $data->{$p} : (isset($data[$p]) ? $data[$p] : null)).']';
		}

		return $pk;
	}

	/**
	 * Get the class's properties
	 *
	 * @return array
	 */
	public static function properties()
	{
		$class = get_called_class();

		// If already determined
		if (array_key_exists($class, static::$_properties_cached))
		{
			return static::$_properties_cached[$class];
		}

		// Fetch the properties if not
		$properties = get_class_vars($class);

		foreach ($properties as $k => $v)
		{
			if (substr($k, 0, 1) == '_')
			{
				unset($properties[$k]);
			}
		}
		static::$_properties_cached[$class] = $properties;

		return static::$_properties_cached[$class];
	}

	/**
	 * Find one or more entries
	 *
	 * @param	mixed
	 * @param	array
	 * @return	object|array
	 */
	public static function find($id = null, array $options = array())
	{
		if (is_null($id))
		{
			return Query::factory(get_called_class());
		}
		elseif ($id == 'all')
		{
			return Query::factory(get_called_class(), $options)->find();
		}
		elseif ($id == 'first' || $id == 'last')
		{
			$options['limit'] = 1;
			$query = Query::factory(get_called_class(), $options);

			foreach(static::primary_key() as $pk)
			{
				$query->order(current(static::primary_key()), $id == 'first' ? 'ASC' : 'DESC');
			}

			return $query->find();
		}
		else
		{
			$cache_pk = $where = array();
			$id = (array) $id;
			foreach (static::primary_key() as $pk)
			{
				$where[] = array($pk, '=', current($id));
				$cache_pk[$pk] = current($id);
				next($id);
			}

			if (array_key_exists(get_called_class(), static::$_cached_objects)
			    and array_key_exists(static::implode_pk($cache_pk), static::$_cached_objects[get_called_class()]))
			{
				return static::$_cached_objects[get_called_class()][static::implode_pk($cache_pk)];
			}

			array_key_exists('where', $options) and $where = array_merge($options['where'], $where);
			return Query::factory(get_called_class(), $options)->find();
		}
	}

	public static function __callStatic($method, $args)
	{
		// find_by_...($val, $options)
		// find_all_by_...($val, $options)
		// count_...($options)
		// min_...($options)
		// max_...($options)
	}

	/* ---------------------------------------------------------------------------
	 * Object usage
	 * --------------------------------------------------------------------------- */

	/**
	 * @var	bool	keeps track of whether it's a new object
	 */
	private $_is_new = true;

	/**
	 * @var	bool	keeps track of whether the object was changed
	 */
	private $_modified = false;

	/**
	 * @var	bool	keeps to object frozen
	 */
	private $_frozen = false;

	/**
	 * @var	array	keeps a copy of the object as it was retrieved from the database
	 */
	private $_original = array();

	/**
	 * @var	array
	 */
	private $_loaded_relations = array();

	/**
	 * Constructor
	 *
	 * @param	array
	 * @param	bool
	 */
	protected function __construct(array $data, $new = true)
	{
		$this->_original = $data;
		foreach ($data as $key => $val)
		{
			$this->{$key} = $val;
		}

		if ($new === false)
		{
			static::$_cached_objects[get_class($this)][static::implode_pk($data)] = $this;
			$this->_is_new = $new;
		}
	}

	/**
	 * Fetch a property or relation
	 *
	 * @param	string
	 * @return	mixed
	 */
	public function & __get($property)
	{
		if (property_exists($this, $property))
		{
			return $this->{$property};
		}
		elseif (isset(static::$_relations) and array_key_exists($property, static::$_relations))
		{
			if ( ! array_key_exists($property, $this->_loaded_relations))
			{
				$this->_loaded_relations[$property] = static::$_relations[$property]->get();
			}
			return $this->_loaded_relations[$property];
		}
		else
		{
			throw new UndefinedProperty('Property "'.$property.'" not found for '.get_called_class().'.');
		}
	}

	/**
	 * Set a property or relation
	 *
	 * @param	string
	 * @param	mixed
	 */
	public function __set($property, $value)
	{
		if ($this->_frozen)
		{
			throw new Exception('Object is frozen, no changes allowed.');
		}

		if (property_exists($this, $property))
		{
			$this->{$property} = $value;
			$this->_modified = true;
		}
		elseif (isset(static::$_relations) and array_key_exists($property, static::$_relations))
		{
			$this->_loaded_relations[$property] = $value;
		}
		else
		{
			throw new UndefinedProperty('Property "'.$property.'" not found for '.get_called_class().'.');
		}
	}

	/**
	 * Save the object and it's relations, create when necessary
	 */
	public function save()
	{
		// save relations first in some way

		return $this->_is_new ? $this->create() : $this->update();
	}

	/**
	 * Save using INSERT
	 */
	public function create() {}

	/**
	 * Save using UPDATE
	 */
	public function update() {

	}

	/**
	 * Delete current object
	 */
	public function delete() {}

	/**
	 * Reset values to those gotten from the database
	 */
	public function reset()
	{
		foreach ($this->_original as $key => $val)
		{
			$this->{$key} = $val;
		}
	}
}

/* End of file model.php */