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
	 * @var  string  table name to overwrite assumption
	 */
	// protected static $_table_name;

	/**
	 * @var  array  relationship properties
	 */
	// protected static $_hasone;
	// protected static $_belongsto;
	// protected static $_hasmany;
	// protected static $_manymany;

	/**
	 * @var  array  name or names of the primary keys
	 */
	protected static $_primary_key = array('id');

	/**
	 * @var  array  cached tables
	 */
	protected static $_table_names_cached = array();

	/**
	 * @var  array  cached properties
	 */
	protected static $_properties_cached = array();

	/**
	 * @var  string  relationships
	 */
	protected static $_relations_cached = array();

	/**
	 * @var  array  array of fetched objects
	 */
	protected static $_cached_objects = array();

	/**
	 * @var  array  array of valid relation types
	 */
	protected static $_valid_relations = array(
		'belongsto'  => 'Orm\\BelongsTo',
		'hasone'     => 'Orm\\HasOne',
		'hasmany'    => 'Orm\\HasMany',
		'manymany'   => 'Orm\\ManyMany'
	);

	public static function factory($data = array(), $new = true)
	{
		return new static($data, $new);
	}

	/**
	 * Get the table name for this class
	 *
	 * @return  string
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
			static::$_table_names_cached[$class] = \Inflector::tableize($class);
		}

		return static::$_table_names_cached[$class];
	}

	/**
	 * Attempt to retrieve an earlier loaded object
	 *
	 * @param   array|Model  $obj
	 * @param   null|string  $class
	 * @return  Model|false
	 */
	public static function cached_object($obj, $class = null)
	{
		$class = $class ?: get_called_class();
		$id    = static::implode_pk($obj);

		return ( ! empty(static::$_cached_objects[$class][$id])) ? static::$_cached_objects[$class][$id] : false;
	}

	/**
	 * Get the primary key(s) of this class
	 *
	 * @return  array
	 */
	public static function primary_key()
	{
		return static::$_primary_key;
	}

	/**
	 * Implode the primary keys within the data into a string
	 *
	 * @param   array
	 * @return  string
	 */
	public static function implode_pk($data)
	{
		if (count(static::$_primary_key) == 1)
		{
			$p = reset(static::$_primary_key);
			return (is_object($data) ? $data->{$p} : (isset($data[$p]) ? $data[$p] : null));
		}

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
	 * @return  array
	 */
	public static function properties()
	{
		$class = get_called_class();

		// If already determined
		if (array_key_exists($class, static::$_properties_cached))
		{
			return static::$_properties_cached[$class];
		}

		// Try to grab the properties from the class...
		if (property_exists($class, '_properties'))
		{
			$properties = static::$_properties;
			foreach ($properties as $key => $p)
			{
				if (is_string($p))
				{
					unset($properties[$key]);
					$properties[$p] = array();
				}
			}
		}

		// ...if the above failed, run DB query to fetch properties
		if (empty($properties))
		{
			$properties = \DB::list_columns(static::table());
		}

		// cache the properties for next usage
		static::$_properties_cached[$class] = $properties;

		return static::$_properties_cached[$class];
	}

	/**
	 * Get the class's relations
	 *
	 * @param   string
	 * @return  array
	 */
	public static function relations($specific = false)
	{
		$class = get_called_class();

		if ( ! array_key_exists($class, static::$_relations_cached))
		{
			$relations = array();
			foreach (static::$_valid_relations as $rel_name => $rel_class)
			{
				if (property_exists($class, '_'.$rel_name))
				{
					foreach (static::${'_'.$rel_name} as $key => $settings)
					{
						$name = is_string($settings) ? $settings : $key;
						$settings = is_array($settings) ? $settings : array();
						$relations[$name] = new $rel_class($class, $name, $settings);
					}
				}
			}

			static::$_relations_cached[$class] = $relations;
		}

		if ($specific === false)
		{
			return static::$_relations_cached[$class];
		}
		else
		{
			if ( ! array_key_exists($specific, static::$_relations_cached[$class]))
			{
				return false;
			}

			return static::$_relations_cached[$class][$specific];
		}
	}

	/**
	 * Find one or more entries
	 *
	 * @param   mixed
	 * @param   array
	 * @return  object|array
	 */
	public static function find($id = null, array $options = array())
	{
		// Return Query object
		if (is_null($id))
		{
			return Query::factory(get_called_class());
		}
		// Return all that match $options array
		elseif ($id == 'all')
		{
			return Query::factory(get_called_class(), $options)->get();
		}
		// Return first or last row that matches $options array
		elseif ($id == 'first' or $id == 'last')
		{
			$query = Query::factory(get_called_class(), $options);

			foreach(static::primary_key() as $pk)
			{
				$query->order($pk, $id == 'first' ? 'ASC' : 'DESC');
			}

			return $query->get_one();
		}
		// Return specific request row by ID
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
			$options['where'] = $where;
			return Query::factory(get_called_class(), $options)->get_one();
		}
	}

	/**
	 * Find one or more entries
	 *
	 * @param   mixed
	 * @param   array
	 * @return  object|array
	 */
	public static function count($id = null, array $options = array())
	{
		return Query::factory(get_called_class(), $options)->count();
	}

	/**
	 * Find the maximum
	 *
	 * @param   mixed
	 * @param   array
	 * @return  object|array
	 */
	public static function max($key = null)
	{
		return Query::factory(get_called_class())->max($key ?: static::primary_key());
	}

	/**
	 * Find the minimum
	 *
	 * @param   mixed
	 * @param   array
	 * @return  object|array
	 */
	public static function min($key = null)
	{
		return Query::factory(get_called_class())->min($key ?: static::primary_key());
	}

	public static function __callStatic($method, $args)
	{
		if ($method == '_init')
		{
			return;
		}

		// Start with count_by? Get counting!
		if (strpos($method, 'count_by') === 0)
		{
			$find_type = 'count';
			$fields = substr($method, 9);
		}

		// Otherwise, lets find stuff
		elseif (strpos($method, 'find_') === 0)
		{
			$find_type = strncmp($method, 'find_all_by_', 12) === 0 ? 'all' : (strncmp($method, 'find_by_', 8) === 0 ? 'first' : false);
			$fields = $find_type === 'first' ? substr($method, 8) : substr($method, 12);
		}

		// God knows, complain
		else
		{
			throw new \Fuel_Exception('Invalid method call.  Method '.$method.' does not exist.', 0);
		}

		$where = $or_where = array();

		if (($and_parts = explode('_and_', $fields)))
		{
			foreach ($and_parts as $and_part)
			{
				$or_parts = explode('_or_', $and_part);

				if (count($or_parts) == 1)
				{
					$where[] = array($or_parts[0] => array_shift($args));
				}
				else
				{
					foreach($or_parts as $or_part)
					{
						$or_where[] = array($or_part => array_shift($args));
					}
				}
			}
		}

		$options = count($args) > 0 ? array_pop($args) : array();

		if ( ! array_key_exists('where', $options))
		{
			$options['where'] = $where;
		}
		else
		{
			$options['where'] = array_merge($where, $options['where']);
		}

		if ( ! array_key_exists('or_where', $options))
		{
			$options['or_where'] = $or_where;
		}
		else
		{
			$options['or_where'] = array_merge($or_where, $options['or_where']);
		}

		if ($find_type == 'count')
		{
			return static::count($options);
		}

		else
		{
			return static::find($find_type, $options);
		}

		// min_...($options)
		// max_...($options)
	}

	/* ---------------------------------------------------------------------------
	 * Object usage
	 * --------------------------------------------------------------------------- */

	/**
	 * @var  bool  keeps track of whether it's a new object
	 */
	private $_is_new = true;

	/**
	 * @var  bool  keeps to object frozen
	 */
	private $_frozen = false;

	/**
	 * @var  array  keeps the current state of the object
	 */
	private $_data = array();

	/**
	 * @var  array  keeps a copy of the object as it was retrieved from the database
	 */
	private $_original = array();

	/**
	 * @var  array
	 */
	private $_loaded_relations = array();

	/**
	 * Constructor
	 *
	 * @param  array
	 * @param  bool
	 */
	protected function __construct(array $data, $new = true)
	{
		$this->_update_original($data);
		foreach ($data as $key => $val)
		{
			$this->{$key} = $val;
		}

		if ($new === false)
		{
			static::$_cached_objects[get_class($this)][static::implode_pk($data)] = $this;
			$this->_is_new = false;
		}
	}

	/**
	 * Update the original setting for this object
	 *
	 * @param  array|null  $original
	 */
	public function _update_original($original = null)
	{
		$original = is_null($original) ? $this->_data : $original;
		foreach ($original as $key => $val)
		{
			$this->_original[$key] = $val;
		}
	}

	/**
	 * Fetch or set relations on this object
	 *
	 * @param   array|null  $rels
	 * @return  void|array
	 */
	public function _relate($rels = null)
	{
		if (is_null($rels))
		{
			return $this->_loaded_relations;
		}
		else
		{
			$this->_loaded_relations = $rels;
		}
	}

	/**
	 * Fetch a property or relation
	 *
	 * @param   string
	 * @return  mixed
	 */
	public function & __get($property)
	{
		if (array_key_exists($property, static::properties()))
		{
			if ( ! array_key_exists($property, $this->_data))
			{
				$this->_data[$property] = null;
			}

			return $this->_data[$property];
		}
		elseif ($rel = static::relations($property))
		{
			if ( ! array_key_exists($property, $this->_loaded_relations))
			{
				$this->_loaded_relations[$property] = $rel->get($this);
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
	 * @param  string
	 * @param  mixed
	 */
	public function __set($property, $value)
	{
		if ($this->_frozen)
		{
			throw new Exception('Object is frozen, no changes allowed.');
		}

		if (in_array($property, static::primary_key()) and $this->{$property} !== null)
		{
			throw new Exception('Primary key cannot be changed.');
		}
		if (array_key_exists($property, static::properties()))
		{
			$this->_data[$property] = $value;
		}
		elseif (static::relations($property))
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
	public function create()
	{
		// Only allow creation with new object, otherwise: clone first, create later
		if ( ! $this->_is_new)
		{
			return false;
		}

		// Set all current values
		$query = Query::factory(get_called_class());
		$primary_key = static::primary_key();
		$properties  = array_keys(static::properties());
		foreach ($properties as $p)
		{
			if ( ! (in_array($p, $primary_key) and is_null($this->{$p})))
			{
				$query->set($p, $this->{$p});
			}
		}

		// Insert!
		$id = $query->insert();

		// when there's one PK it might be auto-incremented, get it and set it
		if (count($primary_key) == 1 and $id !== false)
		{
			$pk = reset($primary_key);
			$this->{$pk} = $id;
		}

		// update the original properties on creation and cache object for future retrieval in this request
		$this->_is_new = false;
		static::$_cached_objects[get_class($this)][static::implode_pk($this)] = $this;
		$this->_update_original();

		return $id !== false;
	}

	/**
	 * Save using UPDATE
	 */
	public function update()
	{
		// New objects can't be updated, neither can frozen
		if ($this->_is_new or $this->_frozen)
		{
			return false;
		}

		// Non changed objects don't have to be saved, but return true anyway (no reason to fail)
		if ( ! $this->is_changed())
		{
			return true;
		}

		// Create the query and limit to primary key(s)
		$query       = Query::factory(get_called_class())->limit(1);
		$primary_key = static::primary_key();
		$properties  = array_keys(static::properties());
		foreach ($primary_key as $pk)
		{
			$query->where($pk, '=', $this->{$pk});
		}

		// Set all current values
		foreach ($properties as $p)
		{
			if ( ! in_array($p, $primary_key))
			{
				$query->set($p, $this->{$p});
			}
		}

		// Return false when update fails
		if ( ! $query->update())
		{
			return false;
		}

		// update the original property on success
		$this->_update_original();

		return true;
	}

	/**
	 * Delete current object
	 */
	public function delete()
	{
		// New objects can't be deleted, neither can frozen
		if ($this->_is_new or $this->_frozen)
		{
			return false;
		}

		// Create the query and limit to primary key(s)
		$query = Query::factory(get_called_class())->limit(1);
		$primary_key = static::primary_key();
		foreach ($primary_key as $pk)
		{
			$query->where($pk, '=', $this->{$pk});
		}

		// Return success of update operation
		if ( ! $query->delete())
		{
			return false;
		}

		if (array_key_exists(get_called_class(), static::$_cached_objects)
			and array_key_exists(static::implode_pk($this), static::$_cached_objects[get_called_class()]))
		{
			unset(static::$_cached_objects[get_called_class()][static::implode_pk($this)]);
		}

		return $this->_original;
	}

	/**
	 * Reset values to those gotten from the database
	 */
	public function reset()
	{
		foreach ($this->_original as $p => $val)
		{
			$this->{$p} = $val;
		}
	}

	/**
	 * Compare current state with the retrieved state
	 *
	 * @param   string|array $property
	 * @return  bool
	 */
	public function is_changed($property = null)
	{
		$property = (array) $property ?: array_keys(static::properties());
		foreach ($property as $p)
		{
			if ($this->{$p} !== $this->_original[$p])
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Allow object cloning to new object
	 */
	public function __clone()
	{
		// Reset primary keys
		foreach (static::$_primary_key as $pk)
		{
			$this->{$pk} = null;
		}

		// This is a new object
		$this->_is_new = true;

		// TODO
		// hasone-belongsto cant be copied and has to be emptied
		// hasmany-belongsto can be copied, ie no change
		// many-many relationships should be copied, ie no change
	}
}

/* End of file model.php */