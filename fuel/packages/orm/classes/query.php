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

class Query {

	public static function factory($model, $options = array())
	{
		return new static($model, $options);
	}

	/**
	 * @var	string	classname of the model
	 */
	protected $model;

	/**
	 * @var	string	table alias
	 */
	protected $alias = 't0';

	/**
	 * @var	array	relations to join on
	 */
	protected $relations = array();

	/**
	 * @var	array	fields to select
	 */
	protected $select = array();

	/**
	 * @var	int		max number of returned rows
	 */
	protected $limit;

	/**
	 * @var	int		offset
	 */
	protected $offset;

	/**
	 * @var	array	where conditions
	 */
	protected $where = array();

	/**
	 * @var	array	or where conditions
	 */
	protected $or_where = array();

	/**
	 * @var	array	order by clauses
	 */
	protected $order_by = array();

	/**
	 * @var	array	group by clauses
	 */
	protected $group_by = array();

	/**
	 * @var	array	values for insert or update
	 */
	protected $values = array();

	protected function __construct($model, $options, $table_alias = null)
	{
		$this->model = $model;

		foreach ($options as $opt => $val)
		{
			if (method_exists($this, $opt))
			{
				call_user_func_array(array($this, $opt), (array) $val);
			}
		}
	}

	/**
	 * Select which properties are included, each as its own param. Or don't give input to retrieve
	 * the current selection.
	 *
	 * @return void|array
	 */
	public function select()
	{
		$fields = func_get_args();

		if (empty($fields))
		{
			if (empty($this->select))
			{
				$fields = call_user_func($this->model.'::properties');

				if (empty($fields))
				{
					throw new Exception('No properties found in model.');
				}
				foreach ($fields as $field)
				{
					$this->select($field);
				}
			}

			$out = array();
			foreach($this->select as $k => $v)
			{
				$out[] = array($v, $k);
			}
			return $out;
		}

		$i = count($this->select);
		foreach ($fields as $val)
		{
			strpos($val, '.') === false ? 't0.'.$val : $val;
			$this->select[$this->alias.'_c'.$i++] = $val;
		}
	}

	/**
	 * Set the limit
	 *
	 * @param	int
	 */
	public function limit($limit)
	{
		$this->limit = intval($limit);

		return $this;
	}

	/**
	 * Set the offset
	 *
	 * @param	int
	 */
	public function offset($offset)
	{
		$this->offset = intval($offset);

		return $this;
	}

	/**
	 * Set where condition
	 *
	 * @param	string	property
	 * @param	string	comparison type (can be omitted)
	 * @param	string	comparison value
	 */
	public function where()
	{
		$condition = func_get_args();
		return $this->_where($condition);
	}

	/**
	 * Set or_where condition
	 *
	 * @param	string	property
	 * @param	string	comparison type (can be omitted)
	 * @param	string	comparison value
	 */
	public function or_where()
	{
		$condition = func_get_args();
		return $this->_where($condition, 'or_where');
	}

	/**
	 * Does the work for where() and or_where()
	 *
	 * @param	array
	 * @param	string
	 */
	public function _where($condition, $type = 'where')
	{
		if (is_array($condition[0]))
		{
			foreach ($condition as $c)
			{
				$this->_where($c, $type);
			}
			return $this;
		}

		if (count($condition) == 2)
		{
			$this->{$type}[] = array($condition[0], '=', $condition[1]);
		}
		elseif (count($condition) == 3)
		{
			$this->{$type}[] = $condition;
		}
		else
		{
			throw new Exception('Invalid param count for where condition.');
		}

		return $this;
	}

	/**
	 * Set the order_by
	 *
	 * @param	string|array
	 * @param	string|null
	 */
	public function order($property, $direction = 'ASC')
	{
		if (is_array($property))
		{
			foreach ($property as $p => $d)
			{
				// Simple array of keys
				if (is_int($p))
				{
					$this->order($d, $direction);
				}

				// Assoc array of orders
				else
				{
					$this->order($p, $d);
				}
			}
			return;
		}

		$this->order_by[$property] = $direction;

		return $this;
	}

	/**
	 * Set a relation to include
	 *
	 * @param	string
	 */
	public function related($relation)
	{
		if (is_array($relation))
		{
			foreach ($relation as $r)
			{
				$this->relation($r);
			}
			return;
		}

		$this->relations[] = $relation;

		return $this;
	}

	/**
	 * Set any properties for insert or update
	 *
	 * @param	string|array
	 * @param	mixed
	 */
	public function set($property, $value = null)
	{
		if (is_array($property))
		{
			foreach ($property as $p => $v)
			{
				$this->set($p, $v);
			}
			return $this;
		}

		$this->values[$property] = $value;

		return $this;
	}

	/**
	 * Build a select, delete or update query
	 *
	 * @param	Database_Query
	 * @param	string|select	either array for select query or string update, delete, insert
	 * @return	array			with keys query and relations
	 */
	public function build_query($query, $columns = array())
	{
		// Set from table
		$query->from(call_user_func($this->model.'::table'));

		// Get the limit
		if ( ! is_null($this->limit))
		{
			$query->limit($this->limit);
		}

		// Get the offset
		if ( ! is_null($this->offset))
		{
			$query->offset($this->offset);
		}

		// Get the order
		if ( ! empty($this->order_by))
		{
			foreach ($this->order_by as $property => $direction)
			{
				if (strpos($property, '.') === false or strpos($property, $this->table_name.'.') === 0)
				{
					$query->order_by($property, $direction);
					unset($this->order_by[$property]);
				}
			}
		}

		// Get the group
		if ( ! empty($this->group_by))
		{
			$query->group_by($this->group_by);
		}

		if ( ! empty($this->where))
		{
			foreach ($this->where as $key => $conditional)
			{
				if (strpos($conditional[0], '.') === false or strpos($conditional[0], $this->table_name.'.') === 0)
				{
					$query->where($conditional[0], $conditional[1], $conditional[2]);
					unset($this->where[$key]);
				}
			}
		}

		if ( ! empty($this->or_where))
		{
			foreach ($this->or_where as $key => $conditional)
			{
				if (strpos($conditional[0], '.') === false or strpos($conditional[0], $this->table_name.'.') === 0)
				{
					$query->or_where($conditional[0], $conditional[1], $conditional[2]);
					unset($this->or_where[$key]);
				}
			}
		}

		$relations = array(); // keeps all the relationship objects
		$joins = array();     // keeps all the join table arrays
		$i = 1;
		foreach ($this->relations as $name => $rel)
		{
			$table = 't'.$i++;
			$relations[$name] = array($rel, $rel->select($table));
			$joins[] = $rel->join($table);
		}

		if ($this->use_subquery())
		{
			// Get the columns for final select
			$columns = $columns;
			foreach ($relations as $properties)
			{
				foreach ($properties[1] as $p => $a)
				{
					$columns[] = array($table.'.'.$a, $p);
				}
			}

			$new_query = call_user_func_array('DB::select', $columns);
			$query = $new_query->from($query);
		}
		else
		{
			foreach ($relations as $properties)
			{
				foreach ($properties[1] as $p => $a)
				{
					$query->select(array($table.'.'.$a, $p));
				}
			}
		}

		foreach ($joins as $join)
		{
			if ( ! array_key_exists('table', $join))
			{
				foreach ($join as $j)
				{
					$join_query = $query->join($j['table'], $j['type']);
					foreach	($j['on'] as $on)
					{
						$join_query->on($on['on'][0], $on['on'][1], $on['on'][2]);
					}
				}
			}
			else
			{
				$join_query = $query->join($join['table'], $join['type']);
				foreach	($join['on'] as $on)
				{
					$join_query->on($on['on'][0], $on['on'][1], $on['on'][2]);
				}
			}
		}

		// Get the order
		if ( ! empty($this->order_by))
		{
			foreach ($this->order_by as $column => $direction)
			{
				$query->order($column, $direction);
			}
		}

		// put omitted where conditions back
		if ( ! empty($this->where))
		{
			foreach ($this->where as $key => $conditional)
			{
				$query->where($conditional[0], $conditional[1], $conditional[2]);
			}
		}

		// put omitted or_where conditions back
		if ( ! empty($this->or_where))
		{
			foreach ($this->or_where as $conditional)
			{
				$query->or_where($conditional[0], $conditional[1], $conditional[2]);
			}
		}

		return array('query' => $query, 'relations' => $relations);
	}

	/**
	 * Determines whether a subquery is needed, is the case if there was a limit/offset on a join
	 *
	 * @return	bool
	 */
	public function use_subquery()
	{
		return ( ! empty($this->relations) and ( ! empty($this->limit) or ! empty($this->offset)));
	}

	/**
	 * Hydrate model instances with retrieved data
	 *
	 * @param	array	row from the database
	 * @param	array	relations to be expected
	 * @param	array	current result array (by reference)
	 * @param	string	model classname to hydrate
	 * @param	array	columns to use
	 */
	public function hydrate($row, $relations, &$result, $model = null, $select = null)
	{
		$model = is_null($model) ? $this->model : $model;
		$select = is_null($select) ? $this->select() : $select;
		$obj = array();
		foreach ($select as $s)
		{
			$obj[$s[0]] = $row[$s[1]];
		}

		if ( ! in_array($pk = $model::implode_pk($obj), $result))
		{
			$obj = $model::factory($obj, false);
			$result[$pk] = $obj;
		}

		foreach ($relations as $rel_name => $rel)
		{
			list($rel, $rel_select) = $rel;
			if ($rel instanceof HasMany or $rel instanceof ManyMany)
			{
				$this->hydrate($row, array(), $obj->{$rel_name}, $rel->model_to, $rel_select);
			}
			else
			{
				$obj->{$rel_name} = $this->hydrate($row, array(), array(), $rel->model_to, $rel_select);
			}
		}

		return $obj;
	}

	/**
	 * Build the query and return it hydrated
	 *
	 * @return	Model
	 */
	public function find()
	{
		// Get the columns
		$columns = $this->select();

		// Start building the query
		$query = call_user_func_array('DB::select', $this->use_subquery() ? array(array_keys($columns)) : $columns);

		// Build the query further
		$tmp       = $this->build_query($query, $columns);
		$query     = $tmp['query'];
		$relations = $tmp['relations'];

		$rows = $query->execute()->as_array();
		$result = array();
		foreach ($rows as $row)
		{
			$this->hydrate($row, $relations, $result);
		}

		// It's all built, now lets execute and start hydration
		return $result;
	}

	/**
	 * Count the result of a query
	 *
	 * @param	bool	false for random selected column or specific column, only works for main model currently
	 * @return	int		number of rows OR false
	 */
	public function count($distinct = false)
	{
		// Get the columns
		$columns = DB::expr('COUNT('.($distinct ? 'DISTINCT ' : '').$this->alias.'.'.($distinct ?: key($this->select)).') AS count_result');

		// Remove the current select and
		$query = call_user_func_array('DB::select', $columns);

		$tmp   = $this->build_query($query, $columns);
		$query = $tmp['query'];
		$count = $query->execute()->get('count_result');

		// Database_Result::get('count_result') returns a string | null
		if ($count === null)
		{
			return false;
		}

		return (int) $count;
	}

	/**
	 * Get the maximum of a column for the current query
	 *
	 * @param	string	column
	 * @return	mixed	maximum value OR false
	 */
	public function max($column)
	{
		// Get the columns
		$columns = DB::expr('MAX('.$this->alias.'.'.$column.') AS max_result');

		// Remove the current select and
		$query = call_user_func_array('DB::select', $columns);

		$tmp   = $this->build_query($query, $columns);
		$query = $tmp['query'];
		$max   = $query->execute()->get('max_result');

		// Database_Result::get('max_result') returns a string | null
		if ($max === null)
		{
			return false;
		}

		return $max;
	}

	/**
	 * Get the minimum of a column for the current query
	 *
	 * @param	string	column
	 * @return	mixed	minimum value OR false
	 */
	public function min($column)
	{
		// Get the columns
		$columns = DB::expr('MIN('.$this->alias.'.'.$column.') AS min_result');

		// Remove the current select and
		$query = call_user_func_array('DB::select', $columns);

		$tmp   = $this->build_query($query, $columns);
		$query = $tmp['query'];
		$min   = $query->execute()->get('min_result');

		// Database_Result::get('min_result') returns a string | null
		if ($min === null)
		{
			return false;
		}

		return $min;
	}

	/**
	 * Run INSERT with the current values
	 *
	 * @return	mixed	last inserted ID or false on failure
	 * @todo	work with relations
	 */
	public function insert()
	{
		$res = DB::insert(call_user_func($this->model.'::table'), array_keys($this->values))
			->values(array_values($this->values))
			->execute();

		// Failed to save the new record
		if ($res[0] === 0)
		{
			return false;
		}

		return $res[0];
	}

	/**
	 * Run UPDATE with the current values
	 *
	 * @return	bool	success of update operation
	 * @todo	work with relations
	 */
	public function update()
	{
		// temporary disable relations and group_by
		$tmp_relations   = $this->relations;
		$this->relations = array();
		$tmp_group_by    = $this->group_by;
		$this->group_by  = array();

		// Build query and execute update
		$query = DB::update(call_user_func($this->model.'::table'));
		$tmp   = $this->build_query($query);
		$query = $tmp['query'];
		$res = $query->set($this->values)->execute();

		// put back any relations/group_by settings
		$this->relations = $tmp_relations;
		$this->group_by  = $tmp_group_by;

		return $res > 0;
	}

	/**
	 * Run DELETE with the current values
	 *
	 * @return	bool	success of delete operation
	 * @todo	cascade option and for relations and make sure they're removed
	 */
	public function delete()
	{
		// temporary disable relations and group_by
		$tmp_relations   = $this->relations;
		$this->relations = array();
		$tmp_group_by    = $this->group_by;
		$this->group_by  = array();

		// Build query and execute update
		$query = DB::delete(call_user_func($this->model.'::table'));
		$tmp   = $this->build_query($query);
		$query = $tmp['query'];
		$res = $query->execute();

		// put back any relations/group_by settings
		$this->relations = $tmp_relations;
		$this->group_by  = $tmp_group_by;

		return $res > 0;
	}
}

/* End of file query.php */