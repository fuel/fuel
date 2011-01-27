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

namespace ActiveRecord;


use \DB;
use \Database;
use \Inflector;

class Model {

	const IS_COUNT = 'IS_COUNT_random_hghj8uyt567uygfvb876trf';

	protected $prefixed_table_name = null;

	/**
	 * Queries the table for the given primary key value ($id).  $id can also
	 * contain 2 special values:
	 *
	 * <ul>
	 * <li>'all' - Will return all of the records in the table</li>
	 * <li>'first' - Will return the first record.  This will set automatically
	 * set the 'limit' option to 1.</li>
	 * </ul>
	 *
	 * The following options are available to use in the $options parameter:
	 *
	 * <ul>
	 * <li>include - an array of associations to include in the query.  Example:
	 * <code>array('group', 'posts')</code></li>
	 * <li>select - an array of columns to select (defaults to '*').  Example:
	 * <code>array('first_name', 'last_name')</code></li>
	 * <li>limit - the maximum number of records to fetch</li>
	 * <li>offset - the offset to start from</li>
	 * <li>order - an array containing the ORDER BY clause.  Example:
	 * <code>array('last_name', 'ASC')</code></li>
	 * <li>where - an array of arrays containing the where clauses.  Example:
	 * <code>array(array('enabled', '=', 1), array('blacklisted', '=', 0))</code>
	 * <li>or_where - identical to 'where' except these are OR WHERE statements.</li>
	 *
	 * Usage:
	 *
	 * <code>$user = User::find(2, array('include' => array('group')));</code>
	 *
	 * @param	int|string	$id			the primary key value
	 * @param	array		$options	the find options
	 * @return	object		the result
	 */
	public static function find($id = 'all', $options = array())
	{
		$instance = new static;
		$results = $instance->run_find($id, $options);
		unset($instance);

		return $results;
	}

	/**
	 * Alias for find('all', ...)
	 *
	 * Usage:
	 *
	 * <code>$user = User::all(array('include' => array('group')));</code>
	 *
	 * @param	array		$options	the find options
	 * @return	object		the result
	 */
	public static function all($options = array())
	{
		return static::find('all', $options);
	}

	/**
	 * Alias for find('first', ...)
	 *
	 * Usage:
	 *
	 * <code>$user = User::all(array('include' => array('group')));</code>
	 *
	 * @param	array		$options	the find options
	 * @return	object		the result
	 */
	public static function first($options = array())
	{
		return static::find('first', $options);
	}

	/**
	 * This allows for queries called by a static method on the model class.  It
	 * supports both 'and' and 'or' queries, or a mixture of both.  You can also
	 * set the last parameter to an array of option. see {@link find} for
	 * available options.
	 *
	 * Usage:
	 *
	 * <code>
	 * Model_User::find_by_group_id(2);
	 * Model_User::find_by_username_and_password('demo', 'password');
	 * Model_User::find_by_username_or_group_id('demo', 2);
	 * Model_User::find_by_email_and_password_or_group_id('demo@example.com', 'password', 2);
	 * </code>
	 *
	 * @param	string	$name		the method name called
	 * @param	array	$arguments	the method arguments
	 * @return	object|array	an instance or array of instances found
	 */
	public static function __callStatic($name, $arguments)
	{
		if ($name == '_init')
		{
			return;
		}
		
		$find_type = strncmp($name, 'find_all_by_', 12) === 0 ? 'all' : (strncmp($name, 'find_by_', 8) === 0 ? 'first' : false);
		
		if ( ! $find_type)
		{
			throw new \Exception('Invalid method call.  Method '.$name.' does not exist.', 0);
		}

		$name = $find_type === 'first' ? substr($name, 8) : substr($name, 12);

		$and_parts = explode('_and_', $name);

		$temp_model = new static;
		$table_name = $temp_model->table_name;
		unset($temp_model);

		$where = array();
		$or_where = array();

		foreach ($and_parts as $and_part)
		{
			$or_parts = explode('_or_', $and_part);
			if (count($or_parts) == 1)
			{
				$where[] = array(
					$table_name.'.'.$or_parts[0], '=', array_shift($arguments)
				);
			}
			else
			{
				foreach($or_parts as $or_part)
				{
					$or_where[] = array(
						$table_name.'.'.$or_part, '=', array_shift($arguments)
					);
				}
			}
		}

		$options = count($arguments) > 0 ? array_pop($arguments) : array();

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

		return static::find($find_type, $options);
	}

	/**
	 * This function is used to re-format the given $row into the $col_lookup
	 * format.
	 *
	 * @param	array	$row		the record
	 * @param	array	$col_lookup	the lookup schema
	 * @return	array	the new $row
	 */
	protected static function transform_row($row, $lookup)
	{
		$object = array();
		foreach ($row as $name => $value)
		{
			$object[$lookup[$name]["table"]][$lookup[$name]["column"]] = $value;
		}
		return $object;
	}

	/**
	 * The table name of the model.  If this is not set in the model, then it
	 * is guessed based on the class name.
	 *
	 * @var	string	the table name
	 */
	protected $table_name = null;

	/**
	 * Holds the class name of the child object that extends ActiveRecord\Model
	 *
	 * @var	string	the class name
	 */
	protected $class_name = null;

	/**
	 * Holds the primary key for the table associated with this model
	 *
	 * @var	string	the primary key
	 */
	public $primary_key = 'id';

	/**
	 * Holds all the columns for this model.  If this is not set in the model,
	 * it attempts to read them in from the table.
	 *
	 * @var	array	the columns
	 */
	protected $columns = array();

	/**
	 * Holds all the data for the record
	 *
	 * @var	array	the date
	 */
	protected $data = array();

	/**
	 * Holds all the associations for the model
	 *
	 * @var	array	the associations
	 */
	protected $associations = array();

	/**
	 * Holds the modified state of the current model.
	 *
	 * @var	bool	the state
	 */
	protected $is_modified = false;

	/**
	 * Holds the frozen state of the model object.  An object is frozen once
	 * it has been deleteed.
	 *
	 * @var	bool	the state
	 */
	protected $frozen = false;

	/**
	 * Holds if this is a new record or not.
	 *
	 * @var	bool	the status
	 */
	public $new_record = true;

	/**
	 * The association types that ActiveRecord supports.
	 *
	 * @var	array	the types
	 */
	private $assoc_types = array('belongs_to', 'has_many', 'has_one');

	/**
	 * The factory takes $params and returns a new instance.
	 *
	 *     $view = Model_User::factory(array('stuff' => 'junk'));
	 *
	 * @param   string  view filename
	 * @param   array   array of values
	 * @return  View
	 */
	public static function factory($params = null)
	{
		return new static($params, true);
	}

	/**
	 * The constructor takes $params which can be data to set for the record.
	 *
	 * Usage:
	 *
	 * <code>
	 * $user = new Model_User;
	 * $user = new Model_User(array('name' => 'Dan'));
	 * </code>
	 *
	 * @param	array	$params			any data to set
	 * @param	bool	$new_record		if this is a new record
	 * @param	bool	$is_modified	if this record is modified
	 */
	public function __construct($params = null, $new_record = true, $is_modified = false)
	{
		$this->class_name = get_class($this);

		if ($this->table_name === null)
		{
			$this->table_name = Inflector::tableize($this->class_name);
		}

		$this->prefixed_table_name = \Database::instance()->table_prefix($this->table_name);

		//don't process associacions when instance was created by static::count() 
		if ($params === self::IS_COUNT)
		{
			return;
		}

		// Setup all the associations
		foreach ($this->assoc_types as $type)
		{
			if (isset($this->{$type}))
			{
				$class_name = 'ActiveRecord\\'.Inflector::classify($type);

				foreach ($this->{$type} as $assoc)
				{
					/* handle association sent in as array with options */
					if (is_array($assoc))
					{
						$key = key($assoc);
						$this->{$key} = new $class_name($this, $key, current($assoc));
					}
					else
					{
						$this->{$assoc} = new $class_name($this, $assoc);
					}
				}
			}
		}

		if (empty($this->columns))
		{
			$this->columns = array_keys(Database::instance()->list_columns($this->table_name));
		}

		if (is_array($params))
		{
			foreach ($params as $key => $value)
			{
				$this->{$key} = $value;
			}
			$this->is_modified = $is_modified;
			$this->new_record = $new_record;
		}
	}

	/**
	 * Attempts to retrieve the column or association given.
	 *
	 * Usage:
	 *
	 * <code>
	 * $user = User::find(1, array('include' => array('group'));
	 * $user->first_name;
	 * $user->group->name;
	 *
	 * $group = Group::find(1, array('include' => array('users'));
	 * $group->users[0]->name;
	 * </code>
	 *
	 * @param	string	$name	the key to retrieve
	 * @return	mixed	the column or association object
	 * @throws	ActiveRecord\Exception
	 */
	public function __get($name)
	{
		if (array_key_exists($name, $this->data))
		{
			return $this->data[$name];
		}
		elseif (array_key_exists($name, $this->associations))
		{
			return $this->associations[$name]->get($this);
		}
		elseif (in_array($name, $this->columns))
		{
			return null;
		}
		elseif (preg_match('/^(.+?)_ids$/', $name, $matches))
		{
			$assoc_name = Inflector::pluralize($matches[1]);
			if ($this->associations[$assoc_name] instanceof HasMany)
			{
				return $this->associations[$assoc_name]->get_ids($this);
			}
		}

		throw new \Exception("attribute called '$name' doesn't exist", Exception::AttributeNotFound);
	}

	/**
	 * Attempts to set the column or association to the geven value.
	 *
	 * Usage:
	 *
	 * <code>
	 * $user = User::find(1, array('include' => array('group'));
	 * $user->first_name = 'Joe';
	 *
	 * $group = Group::find(1, array('include' => array('users'));
	 * $group->users[0]->first_name = 'Dave';
	 * </code>
	 *
	 * @param	string	$name	the key to set
	 * @param	mixed	$value	the value to set
	 * @throws	ActiveRecord\Exception
	 */
	public function __set($name, $value)
	{
		if ($this->frozen)
		{
			throw new \Exception("Can not update $name as object is frozen.", Exception::ObjectFrozen);
		}

		if (preg_match('#(.+?)_ids$#', $name, $matches))
		{
			$assoc_name = Inflector::pluralize($matches[1]);
		}

		if (in_array($name, $this->columns))
		{
			$this->data[$name] = $value;
			$this->is_modified = true;
		}
		elseif ($value instanceof Association)
		{
			$this->associations[$name] = $value;
		}
		elseif (array_key_exists($name, $this->associations))
		{
			/* call like $comment->post = $mypost */
			$this->associations[$name]->set($value, $this);
		}
		elseif (isset($assoc_name)
				&& array_key_exists($assoc_name, $this->associations)
				&& $this->associations[$assoc_name] instanceof HasMany)
		{
			/* allow for $p->comment_ids type sets on HasMany associations */
			$this->associations[$assoc_name]->set_ids($value, $this);
		}
		else
		{
			throw new \Exception("attribute called '$name' doesn't exist", Exception::AttributeNotFound);
		}
	}

	/**
	 * On any ActiveRecord object we can make method calls to a specific assoc.
	 *
	 * Usage:
	 *
	 * <code>
	 * $p = Post::find(1, array('include' => array('comments'));
	 * $p->comments_push($comment);
	 * </code>
	 *
	 * @param	string	$name	the method name called
	 * @param	array	$args	the method arguments
	 * @return	mixed	the result of the called method
	 * @throws	ActiveRecord\Exception
	 */
	public function __call($name, $args)
	{
		// find longest available association that matches beginning of method
		$longest_assoc = '';
		foreach (array_keys($this->associations) as $assoc)
		{
			if (strpos($name, $assoc) === 0 &&
					strlen($assoc) > strlen($longest_assoc))
			{
				$longest_assoc = $assoc;
			}
		}

		if ($longest_assoc !== '')
		{
			list($null, $func) = explode($longest_assoc . '_', $name, 2);
			return $this->associations[$longest_assoc]->$func($args, $this);
		}
		else
		{
			throw new \Exception("method or association not found for ($name)", Exception::MethodOrAssocationNotFound);
		}
	}

	/**
	 * Gets tbe columns for the current model
	 *
	 * Usage:
	 *
	 * <code>
	 * $user = new User;
	 * $user->get_columns();
	 * </code>
	 *
	 * @return	array	the columns
	 */
	public function get_columns()
	{
		return $this->columns;
	}

	/**
	 * Gets tbe primary key for the current model
	 *
	 * Usage:
	 *
	 * <code>
	 * $user = new User;
	 * $user->get_primary_key();
	 * </code>
	 *
	 * @return	string	the primary key
	 */
	public function get_primary_key()
	{
		return $this->primary_key;
	}

	/**
	 * Checks if the instance is frozen or not
	 *
	 * Usage:
	 *
	 * <code>
	 * $user = User::find(3);
	 * $user->delete();
	 * $user->is_frozen(); // Returns true
	 * </code>
	 *
	 * @return	bool	frozen status
	 */
	public function is_frozen()
	{
		return $this->frozen;
	}

	/**
	 * Checks if the instance is a new record or not
	 *
	 * Usage:
	 *
	 * <code>
	 * $user = new User;
	 * $user->is_new_record(); // Returns true
	 * </code>
	 *
	 * @return	bool	new record status
	 */
	public function is_new_record()
	{
		return $this->new_record;
	}

	/**
	 * Checks if the intance has been modified
	 *
	 * Usage:
	 *
	 * <code>
	 * $user = User::find(2);
	 * $user->is_modified(); // Returns false
	 *
	 * $user->name = "Joe";
	 * $user->is_modified(); // Returns true
	 * </code>
	 *
	 * @return	array	the columns
	 */
	public function is_modified()
	{
		return $this->is_modified;
	}

	/**
	 * Sets the modified status
	 *
	 * Usage:
	 *
	 * <code>
	 * $user = User::find(2);
	 * $user->set_modified(true);
	 * </code>
	 */
	public function set_modified($val)
	{
		$this->is_modified = $val;
	}

	/**
	 * Turns the current record as well as all associations into an accociative
	 * array.
	 *
	 * Usage:
	 *
	 * <code>
	 * $user = User::find(2);
	 * $user_array = $user->as_array();
	 * </code>
	 *
	 * @return	array	the object in the form of an array
	 */
	public function as_array()
	{
		$return = $this->data;

		foreach ($this->associations as $key => $val)
		{
			$assoc = $this->associations[$key]->get($this);

			if ( ! is_array($assoc))
			{
				$return[$key] = $assoc;
			}
			else
			{
				foreach ($assoc as $row)
				{
					$return[$key][] = $row->data;
				}
			}
		}

		return $return;
	}

	/**
	 * Updates the given data then saves the record
	 *
	 * Usage:
	 *
	 * <code>
	 * $user = User::find(2);
	 * $user->update(array('name' => 'Joe'));
	 * </code>
	 *
	 * @return	bool	save status
	 */
	public function update($attributes)
	{
		foreach ($attributes as $key => $value)
		{
			$this->$key = $value;
		}

		return $this->save();
	}

	/**
	 * Saves the current record and any associations.  This method will call 6
	 * "hook" methods that you can use to modify the data, run extra validation
	 * or anything else you want.  They are as follows:
	 *
	 * <ul>
	 * <li>before_save() - runs before anything</li>
	 * <li>before_create() - runs before any new record is entered</li>
	 * <li>after_create() - runs after new record creation</li>
	 * <li>before_update() - runs before a modified record is saved</li>
	 * <li>after_update() - runs after a modified record is saved</li>
	 * <li>after_save() - runs after everything, new or modified records</li>
	 * </ul>
	 *
	 * Usage:
	 *
	 * <code>
	 * $user = User::find(3);
	 * $user->first_name = 'Dan';
	 * $user->save();
	 * </code>
	 *
	 * @return	bool	the status of the operation
	 */
	public function save()
	{
		if (method_exists($this, 'before_save'))
		{
			$this->before_save();
		}

		foreach ($this->associations as $name => $assoc)
		{
			if ($assoc instanceOf BelongsTo && $assoc->needs_saving())
			{
				$this->$name->save();
				/* after our save, $this->$name might have new id;
				  we want to update the foreign key of $this to match;
				  we update this foreign key already as a side-effect
				  when calling set() on an association
				 */
				$this->$name = $this->$name;
			}
		}
		if ($this->new_record)
		{
			if (method_exists($this, 'before_create'))
			{
				$this->before_create();
			}
			$columns = array();
			foreach ($this->columns as $column)
			{
				if ($column == $this->primary_key)
				{
					continue;
				}
				$columns[] = $column;
				if (is_null($this->$column))
				{
					$values[] = 'NULL';
				}
				else
				{
					$values[] = $this->$column;
				}
			}
			$res = DB::insert($this->table_name, $columns)
					->values($values)
					->execute();

			// Failed to save the new record
			if ($res[0] === 0)
			{
				return false;
			}

			$this->{$this->primary_key} = $res[0];
			$this->new_record = false;
			$this->is_modified = false;

			if (method_exists($this, 'after_create'))
			{
				$this->after_create();
			}
		}
		elseif ($this->is_modified)
		{
			if (method_exists($this, 'before_update'))
			{
				$this->before_update();
			}

			$values = array();

			foreach ($this->columns as $column)
			{
				if ($column == $this->primary_key)
				{
					continue;
				}
				$values[$column] = is_null($this->$column) ? 'NULL' : $this->$column;
			}
			$res = DB::update($this->table_name)
						->set($values)
						->where($this->primary_key, '=', $this->{$this->primary_key})
						->limit(1)
						->execute();

			$this->new_record = false;
			$this->is_modified = false;

			if (method_exists($this, 'after_update'))
			{
				$this->after_update();
			}
		}

		foreach ($this->associations as $name => $assoc)
		{
			if ($assoc instanceOf HasOne && $assoc->needs_saving())
			{
				/* again sorta weird, this will update foreign key as needed */
				$this->$name = $this->$name;
				/* save the object referenced by this association */
				$this->$name->save();
			}
			elseif ($assoc instanceOf HasMany && $assoc->needs_saving())
			{
				$assoc->save_as_needed($this);
			}
		}

		if (method_exists($this, 'after_save'))
		{
			$this->after_save();
		}

		return true;
	}

	/**
	 * deletes (deletes) the current record and any associations, then freezes
	 * the record so it cannot be modified again.  This method will call 2
	 * "hook" methods that you can use to doanything else you want.  They are as
	 * follows:
	 *
	 * <ul>
	 * <li>before_delete() - runs before the record is deleteed</li>
	 * <li>after_delete() - runs after the record is deleteed</li>
	 * </ul>
	 *
	 * Usage:
	 *
	 * <code>
	 * $user = User::find(3);
	 * $user->delete();
	 * </code>
	 *
	 * @return	bool	the status of the operation
	 */
	public function delete($id = null)
	{
		// Not running statically, AND
		/*
		if ( ! isset($this) AND empty($id))
		{
			throw new Exception('Which record should be deleted?', Exception::AttributeNotFound);
			return false;
		}

		else
		{
			$obj = $id ? static::run_find($id) : $this;
		}
		*/

		if (method_exists($this, 'before_delete'))
		{
			$this->before_delete();
		}
		foreach ($this->associations as $name => $assoc)
		{
			$assoc->delete($this);
		}

		DB::delete($this->table_name)
			->where($this->primary_key, '=', $this->{$this->primary_key})
			->limit(1)
			->execute();

		$this->frozen = true;

		if (method_exists($this, 'after_delete'))
		{
			$this->after_delete();
		}
		return true;
	}

	/**
	 * Runs the find query.  This is called and used by the {@link find} method,
	 * and is separated out for simplicity.
	 *
	 * @param	int|string|array	$id			the primary key(s) to look up
	 * @param	array				$options	a myriad of options
	 * @return	array	the array of rows
	 */
	protected function run_find($id, $options = array())
	{
		$query = $this->find_query($id, $options);
		$rows = $query['result']->as_array();

		$base_objects = array();
		foreach ($rows as $row)
		{
			if (count($query['column_lookup']) > 0)
			{
				$foreign_keys = array();
				$objects = static::transform_row($row, $query['column_lookup']);
				$ob_key = md5(serialize($objects[$this->table_name]));

				/* set cur_object to base object for this row; reusing if possible */
				if (array_key_exists($ob_key, $base_objects))
				{
					$cur_object = $base_objects[$ob_key];
				}
				else
				{
					$cur_object = new $this->class_name($objects[$this->table_name], false);
					$base_objects[$ob_key] = $cur_object;
				}

				foreach ($objects as $table => $attributes)
				{
					if ($table == $this->table_name)
					{
						continue;
					}
					foreach ($cur_object->associations as $assoc_name => $assoc)
					{
						$assoc->populate_from_find($attributes);
					}
				}
			}
			else
			{
				$item = new $this->class_name($row, false);
				array_push($base_objects, $item);
			}
		}
		if (count($base_objects) == 0 && (is_array($id) || is_numeric($id)))
		{
			throw new \Exception("Couldn't find anything.", Exception::RecordNotFound);
		}

		return (is_array($id) || $id == 'all')
				? array_values($base_objects)
				: array_shift($base_objects);
	}

	/**
	 * Generates then executes the find query.  This is used by {@link run_find}.
	 * Please see {@link find} for parameter options and usage.
	 *
	 * @param	string|int	$id			the primary key to find
	 * @param	array		$options	the array of options
	 * @return	array	an array containing the query and column lookup map
	 */
	protected function find_query($id, $options = array())
	{
		$item = new $this->class_name;

		($id == 'first') and $options['limit'] = 1;

		$select = array_key_exists('select', $options)  ? $options['select'] : array();

		$joins = array();
		$column_lookup = array();
		if (isset($options['include']))
		{
			$tables_to_columns = array();

			if (is_string($options['include']))
			{
				$includes = array_map('trim', explode(',', $options['include']));
			}
			else
			{
				$includes = $options['include'];
			}

			array_push($tables_to_columns, array(
				$this->table_name => $item->get_columns()
			));

			// get join part of query from association and column names
			foreach ($includes as $include)
			{
				if (isset($item->associations[$include]))
				{
					list($cols, $join) = $item->associations[$include]->join();
					array_push($joins, $join);
					array_push($tables_to_columns, $cols);
				}
			}

			foreach ($tables_to_columns as $table_key => $columns)
			{
				foreach ($columns as $table => $cols)
				{
					foreach ($cols as $key => $col)
					{
						// Compare the values currently in $select to see if we need to remove them
						if ($this->table_name == $table)
						{
								$found_key = array_search($col, $select);
								if ($found_key !== false)
								{
									unset($select[$found_key]);
								}
						}

						// Add this to the select array
						array_push($select, array($table.'.'.$col, "t{$table_key}_r$key"));

						$column_lookup["t{$table_key}_r{$key}"]["table"] = $table;
						$column_lookup["t{$table_key}_r{$key}"]["column"] = $col;
					}
				}
			}
		}

		// Start building the query
		if ( ! empty($joins) and ( ! empty($options['limit']) or ! empty($options['offset'])))
		{
			$query = DB::select('*');
			$fullquery = call_user_func_array('DB::select', $select);
		}
		else
		{
			$query = call_user_func_array('DB::select', $select);
		}

		// Set from table
		$query->from($this->table_name);

		// Get the limit
		if (array_key_exists('limit', $options) and is_numeric($options['limit']))
		{
			$query->limit($options['limit']);
		}

		// Get the offset
		if (array_key_exists('offset', $options) and is_numeric($options['offset']))
		{
			$query->offset($options['offset']);
		}

		// Get the order
		if (array_key_exists('order', $options) && is_array($options['order']))
		{
			if (is_int(key($options['order'])))
			{
				$options['order'][$options['order'][0]] = $options['order'][1];
				unset($options['order'][0], $options['order'][1]);
			}

			foreach ($options['order'] as $column => $direction)
			{
				if (strpos($column, '.') === false or strpos($column, $this->table_name.'.') === 0)
				{
					$query->order_by($column, $direction);
					unset($options['order'][$column]);
				}
			}
		}

		// Get the group
		if (array_key_exists('group', $options))
		{
			$query->group_by($options['group']);
		}
		if (is_array($id))
		{
			$query->where($item->primary_key, 'IN', $id);
		}
		elseif ($id != 'all' && $id != 'first')
		{
			$query->where($this->table_name.'.'.$item->primary_key, '=', $id);;
		}

		if (array_key_exists('where', $options) and is_array($options['where']))
		{
			foreach ($options['where'] as $key => $conditional)
			{
				if (strpos($conditional[0], '.') === false or strpos($conditional[0], $this->table_name.'.') === 0)
				{
					$query->where($conditional[0], $conditional[1], $conditional[2]);
					unset($options['where'][$key]);
				}
			}
		}

		if (array_key_exists('or_where', $options) and is_array($options['or_where']))
		{
			foreach ($options['or_where'] as $key => $conditional)
			{
				if (strpos($conditional[0], '.') === false or strpos($conditional[0], $this->table_name.'.') === 0)
				{
					$query->or_where($conditional[0], $conditional[1], $conditional[2]);
					unset($options['or_where'][$key]);
				}
			}
		}

		// if there was a limit/offset on a join the query up till now will become a subquery
		if ( ! empty($fullquery))
		{
			$fullquery->from(array($query, $this->table_name));
			$query = $fullquery;
		}

		foreach ($joins as $join)
		{
			if ( ! array_key_exists('table', $join))
			{
				foreach ($join as $j)
				{
					$query->join($j['table'], $j['type'])
						  ->on($j['on'][0], $j['on'][1], $j['on'][2]);
				}
			}
			else
			{
				$query->join($join['table'], $join['type'])
					  ->on($join['on'][0], $join['on'][1], $join['on'][2]);
			}
		}

		// Get the order
		if (array_key_exists('order', $options) && is_array($options['order']))
		{
			foreach ($options['order'] as $column => $direction)
			{
				$query->order_by($column, $direction);
				unset($options['order'][$column]);
			}
		}

		// put omitted where conditions back
		if (array_key_exists('where', $options) and is_array($options['where']))
		{
			foreach ($options['where'] as $key => $conditional)
			{
				$query->where($conditional[0], $conditional[1], $conditional[2]);
			}
		}

		// put omitted or_where conditions back
		if (array_key_exists('or_where', $options) and is_array($options['or_where']))
		{
			foreach ($options['or_where'] as $conditional)
			{
				$query->or_where($conditional[0], $conditional[1], $conditional[2]);
			}
		}

		// It's all built, now lets execute
		$result = $query->execute();

		return array('result' => $result, 'column_lookup' => $column_lookup);
	}
	
	/**
	 * Exactly as find() but returns the row count see {@link find} 
	 * all the parameters and options are exactly the same as for find()
	 *
	 * Usage:
	 *
	 * <code>$user = Model_User::count(array('include' => array('group')));</code>
	 *
	 * @param	int|string	$id			the primary key value
	 * @param	array		$options	the find options
	 * @return	int|null 	the row count or null
	 */
	public static function count($options = array())
	{
		$instance = new static(self::IS_COUNT);
		$count = $instance->count_query($options);
		unset($instance);

		return $count;
	}
	
	/**
	 * Generates then executes the count query.  This is used by {@link count}.
	 * Please see {@link count} for parameter options and usage.
	 *
	 * @param	string|int	$id			the primary key to find
	 * @param	array		$options	the array of options
	 * @return	int|null 	the row count or null
	 */
	protected function count_query($options = array())
	{
		// Start building the query
		$query = DB::select(DB::expr('COUNT('.$this->prefixed_table_name.'.'.$this->primary_key.') AS count_result'));
	
		$query->from($this->table_name);

		// Get the group
		if (array_key_exists('group', $options))
		{
			$query->group_by($options['group']);
		}

		if (array_key_exists('where', $options) and is_array($options['where']))
		{
			foreach ($options['where'] as $conditional)
			{
				$query->where($conditional[0], $conditional[1], $conditional[2]);
			}
		}

		if (array_key_exists('or_where', $options) and is_array($options['or_where']))
		{
			foreach ($options['or_where'] as $conditional)
			{
				$query->or_where($conditional[0], $conditional[1], $conditional[2]);
			}
		}

		// It's all built, now lets execute
		$count = $query->execute()->get('count_result');
		
		// Database_Result::get('mycount') returns a string | null
		if ($count === null)
		{
			return null;
		}

		return (int) $count;
	}
}


/* End of file model.php */