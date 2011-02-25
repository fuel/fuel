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

	protected function __construct($model, $options, $table_alias)
	{
		$this->model = $model;

		foreach ($options as $opt => $val)
		{
			if (method_exists($this, $opt))
			{
				call_user_func_array(array($this, $opt), $val);
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
	}

	/**
	 * Set the offset
	 *
	 * @param	int
	 */
	public function offset($offset)
	{
		$this->offset = intval($offset);
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
			return;
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
	}

	/**
	 * Set the order_by
	 *
	 * @param	string|array
	 * @param	string|null
	 */
	public function order_by($property, $direction = null)
	{
		if (is_array($property))
		{
			foreach ($property as $p => $d)
			{
				$this->order_by($p, $d);
			}
			return;
		}

		$this->order_by[$property] = $direction;
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

		$this->relations[] = $r;
	}

	/**
	 * Build the query and return it hydrated
	 *
	 * @return	Model
	 */
	public function find()
	{
		// Get the columns
		$select = $this->select();

		// Start building the query
		$query = call_user_func_array('DB::select', $select);

		// Set from table
		$query->from($this->model::table());

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

		// if there was a limit/offset on a join the query up till now will become a subquery
		if ( ! empty($this->relations) and ( ! empty($this->limit) or ! empty($this->offset)))
		{
			$relations = array();
			$i = 1;
			foreach ($this->relations as $name => $rel)
			{
				$table = 't'.$i++;
				$relations[$name] = array($rel, $rel->select($table));
				$joins[] = $rel->join($table);
			}
			foreach ($relations as $properties)
			{
				foreach ($properties[1] as $p => $a)
				{
					$select[] = array($table.'.'.$a, $p);
				}
			}

			$new_query = call_user_func_array('DB::select', $select);
			$query = $new_query->from($query);
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
				$query->order_by($column, $direction);
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

		$rows = $query->execute()->as_array();
		$result = array();
		foreach ($rows as $row)
		{
			$this->hydrate($row, $relations, $result);
		}

		// It's all built, now lets execute and start hydration
		return $result;
	}

	public function hydrate($row, $relations, &$result, $model = null, $select = null)
	{
		$model = is_null($model) ? $this->model : $model;
		$select = is_null($select) ? $this->select() : $select;
		$obj = array();
		foreach ($select as $k => $v)
		{
			$obj[$v] = $row[$k];
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

	public function count($distinct = false) {}

	public function max() {}

	public function min() {}
}

/* End of file query.php */