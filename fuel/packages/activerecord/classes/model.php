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

use Fuel\Application as App;
use Fuel\Application\DB;

class Model {

	protected static $class = null;

	protected $columns = array();

	protected $attributes = array();

	protected $associations = array();

	protected $is_modified = false;

	protected $frozen = false;

	protected static $primary_key = 'id';

	protected $table_name;

	public $new_record = true;

	private $assoc_types = array('belongs_to', 'has_many', 'has_one');

	public function __construct($params=null, $new_record=true, $is_modified=false)
	{
		// Setup all the associations
		foreach ($this->assoc_types as $type)
		{
			if (isset($this->{$type}))
			{
				$class_name = 'ActiveRecord\\'.App\Inflector::classify($type);

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

		$this->table_name = App\Inflector::tableize(get_called_class());

		$this->columns = array_keys(App\Database::instance()->list_columns($this->table_name));

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

	public function __get($name)
	{
		if (array_key_exists($name, $this->attributes))
		{
			return $this->attributes[$name];
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
			/* allow for $p->comment_ids type gets on HasMany associations */
			$assoc_name = App\Inflector::pluralize($matches[1]);
			if ($this->associations[$assoc_name] instanceof HasMany)
			{
				return $this->associations[$assoc_name]->get_ids($this);
			}
		}
		
		throw new Exception("attribute called '$name' doesn't exist", Exception::AttributeNotFound);
	}

	public function __set($name, $value)
	{
		if ($this->frozen)
		{
			throw new Exception("Can not update $name as object is frozen.", Exception::ObjectFrozen);
		}

		/* allow for $p->comment_ids type sets on HasMany associations */
		if (preg_match('#(.+?)_ids$#', $name, $matches))
		{
			$assoc_name = App\Inflector::pluralize($matches[1]);
		}

		if (in_array($name, $this->columns))
		{
			$this->attributes[$name] = $value;
			$this->is_modified = true;
		}
		elseif ($value instanceof Association)
		{
			/* call from constructor to setup association */
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
			throw new Exception("attribute called '$name' doesn't exist", Exception::AttributeNotFound);
		}
	}

	/* on any ActiveRecord object we can make method calls to a specific assoc.
	  Example:
	  $p = Post::find(1);
	  $p->comments_push($comment);
	  This calls push([$comment], $p) on the comments association
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
			throw new Exception("method or association not found for ($name)", Exception::MethodOrAssocationNotFound);
		}
	}


	public function get_columns()
	{
		return $this->columns;
	}

	public function get_primary_key()
	{
		return static::$primary_key;
	}

	public function is_frozen()
	{
		return $this->frozen;
	}

	public function is_new_record()
	{
		return $this->new_record;
	}

	public function is_modified()
	{
		return $this->is_modified;
	}

	public function set_modified($val)
	{
		$this->is_modified = $val;
	}

	public function update_attributes($attributes)
	{
		foreach ($attributes as $key => $value)
		{
			$this->$key = $value;
		}

		return $this->save();
	}

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
				if ($column == static::$primary_key)
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
			$res = DB::insert($this->table_name, $columns)->values($values)->execute();

			// Failed to save the new record
			if ($res[0] === 0)
			{
				return false;
			}

			$this->{static::$primary_key} = $res[0];
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
				if ($column == static::$primary_key)
				{
					continue;
				}
				$values[$column] = is_null($this->$column) ? 'NULL' : $this->$column;
			}
			$res = DB::update($this->table_name)
						->set($values)
						->where(static::$primary_key, '=', $this->{static::$primary_key})
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

	public function destroy()
	{
		if (method_exists($this, 'before_destroy'))
		{
			$this->before_destroy();
		}
		foreach ($this->associations as $name => $assoc)
		{
			$assoc->destroy($this);
		}

		DB::delete($this->table_name)
				->where(static::$primary_key, '=', $this->{static::$primary_key})
				->limit(1)
				->execute();

		$this->frozen = true;
		
		if (method_exists($this, 'after_destroy'))
		{
			$this->after_destroy();
		}
		return true;
	}

	/**
	 * A static constructor that gets called by the autoloader.  Gets the class
	 * name of the model that was loaded.
	 *
	 * @access	public
	 * @return	void
	 */
	public static function _init()
	{
		static::$class = get_called_class();
	}

	/* transform_row -- transforms a row into its various objects
	  accepts: row from SQL query (array), lookup array of column names
	  return: object keyed by table names and real columns names
	 */

	static function transform_row($row, $col_lookup)
	{
		$object = array();
		foreach ($row as $col_name => $col_value)
		{
			/* set $object["table_name"]["column_name"] = $col_value */
			$object[$col_lookup[$col_name]["table"]][$col_lookup[$col_name]["column"]] = $col_value;
		}
		return $object;
	}

	public static function find($id, $options = array())
	{
		$table_name = App\Inflector::tableize(get_called_class());
		$query = static::_find_query($table_name, $id, $options);
		$rows = $query['result']->as_array();

		$base_objects = array();
		foreach ($rows as $row)
		{
			/* if we've done a join we have some fancy footwork to do
			  we're going to process one rows at a time.
			  each row has a "base" object and objects that've been joined.
			  the base object is whatever class we've been passed as $class.
			  we only want to create one instance of each unique base object.
			  as we see more rows we may be re-using an exising base object to
			  append more join objects to its association.
			 */
			if (count($query['column_lookup']) > 0)
			{
				$objects = static::transform_row($row, $query['column_lookup']);
				$ob_key = md5(serialize($objects[$table_name]));

				/* set cur_object to base object for this row; reusing if possible */
				if (array_key_exists($ob_key, $base_objects))
				{
					$cur_object = $base_objects[$ob_key];
				}
				else
				{
					$cur_object = new static($objects[$table_name], false);
					$base_objects[$ob_key] = $cur_object;
				}

				/* now add association data as needed */
				foreach ($objects as $table_name => $attributes)
				{
					if ($table_name == App\Inflector::tableize(get_called_class()))
					{
						continue;
					}
					foreach ($cur_object->associations as $assoc_name => $assoc)
					{
						if ($table_name == App\Inflector::pluralize($assoc_name))
						{
							$assoc->populate_from_find($attributes);
						}
					}
				}
			}
			else
			{
				$item = new static($row, false);
				array_push($base_objects, $item);
			}
		}
		if (count($base_objects) == 0 && (is_array($id) || is_numeric($id)))
		{
			throw new Exception("Couldn't find anything.", Exception::RecordNotFound);
		}
		return (is_array($id) || $id == 'all') ?
				array_values($base_objects) :
				array_shift($base_objects);
	}

	protected static function _find_query($table_name, $id, $options = array())
	{
		$item = new static;

		($id == 'first') and $options['limit'] = 1;

		$select = array_key_exists('select', $options)  ? $options['select'] : array();

		$joins = array();
		$column_lookup = array();
		if (isset($options['include']))
		{
			$tables_to_columns = array();
			$includes = array_map('trim', explode(',', $options['include']));

			array_push($tables_to_columns, array(App\Inflector::tableize(get_class($item)) => $item->get_columns()));

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
						// Add this to the select array
						array_push($select, array($table.'.'.$col, "t{$table_key}_r$key"));

						$column_lookup["t{$table_key}_r{$key}"]["table"] = $table;
						$column_lookup["t{$table_key}_r{$key}"]["column"] = $col;
					}
				}
			}
		}

		// Start building the query
		$query = call_user_func_array('DB::select', $select);

		$query->from($table_name);

		foreach ($joins as $join)
		{
			if ( ! array_key_exists('table', $join))
			{
				foreach ($join as $j)
				{
					$query->join($j['table'], $j['type'])->on($j['on'][0], $j['on'][1], $j['on'][2]);
				}
			}
			else
			{
				$query->join($join['table'], $join['type'])->on($join['on'][0], $join['on'][1], $join['on'][2]);
			}
		}

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
			$query->order_by($options['order'][0], $options['order'][1]);
		}

		// Get the group
		if (array_key_exists('group', $options))
		{
			$query->group_by($options['group']);
		}
		if (is_array($id))
		{
			$query->where(static::$primary_key, 'IN', $id);
		}
		elseif ($id != 'all' && $id != 'first')
		{
			$query->where($table_name.'.'.static::$primary_key, '=', $id);;
		}

		if (array_key_exists('where', $options) and is_array($options['where']))
		{
			foreach ($options['where'] as $conditional)
			{
				$query->where($conditional[0], $conditional[1], $conditional[2]);
			}
		}



		// It's all built, now lets execute
		$result = $query->execute();

		return array('result' => $result, 'column_lookup' => $column_lookup);
	}
}


/* End of file model.php */