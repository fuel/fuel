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

class ManyMany extends Relation {

	protected $key_from = array('id');

	protected $key_to = array('id');

	/**
	 * @var  string  classname of model to use as connection
	 */
	protected $model_through;

	/**
	 * @var  string  table name of table to use as connection, alternative to $model_through setting
	 */
	protected $table_through;

	/**
	 * @var  string  foreign key of from model in connection table
	 */
	protected $key_through_from;

	/**
	 * @var  string  foreign key of to model in connection table
	 */
	protected $key_through_to;

	public function __construct($from, $name, array $config)
	{
		$this->model_from  = $from;
		$this->model_to    = array_key_exists('model_to', $config) ? $config['model_to'] : 'Model_'.\Inflector::classify($name);
		$this->key_from    = array_key_exists('key_from', $config) ? (array) $config['key_from'] : $this->key_from;
		$this->key_to      = array_key_exists('key_to', $config) ? (array) $config['key_to'] : $this->key_to;

		// Allow for many-many through another object...
		if ( ! empty($config['through']['model']))
		{
			$this->model_through = $config['through']['model'];
		}
		// ...or allow for many-many matching with simple 2 column table
		elseif ( ! empty($config['through']['table']))
		{
			$this->table_through = $config['through']['table'];
		}
		else
		{
			$table_name = array($this->model_from, $this->model_to);
			natcasesort($table_name);
			$table_name = array_merge($table_name);
			$this->table_through = \Inflector::tableize($table_name[0]).'_'.\Inflector::tableize($table_name[1]);
		}
		$this->key_through_from = ! empty($config['through']['key_from'])
			? (array) $config['through']['key_from'] : (array) \Inflector::foreign_key($this->model_from);
		$this->key_through_to = ! empty($config['through']['key_to'])
			? (array) $config['through']['key_to'] : (array) \Inflector::foreign_key($this->model_to);
	}

	public function get(Model $from)
	{
	}

	public function select($table)
	{
		$props = call_user_func(array($this->model_to, 'properties'));
		$i = 0;
		$properties = array();
		foreach ($props as $pk => $pv)
		{
			$properties[] = array($table.'.'.$pk, $table.'_c'.$i);
			$i++;
		}

		if (empty($this->model_through))
		{
			foreach ($this->key_through_to as $to)
			{
				$properties[] = array($table.'_through.'.$to, $table.'_through_c'.$i);
				$i++;
			}
			foreach ($this->key_through_from as $from)
			{
				$properties[] = array($table.'_through.'.$from, $table.'_through_c'.$i);
				$i++;
			}
		}
		else
		{
			$rel = call_user_func(array($this->model_from, 'relations'), $this->model_through);
			$props = call_user_func(array($rel->model_to, 'properties'));
			foreach ($props as $pk => $pv)
			{
				$properties[] = array($table.'_through.'.$pk, $table.'_through_c'.$i);
				$i++;
			}
		}

		return $properties;
	}

	public function join($alias_from, $alias_to)
	{
		if (empty($this->model_through))
		{
			$through_table = $this->table_through;
		}
		else
		{
			$rel = call_user_func(array($this->model_from, 'relations'), $this->model_through);
			$through_table = call_user_func(array($rel->model_to, 'table'));
		}

		$join = array(
			array(
				'table'	=> array($through_table, $alias_to.'_through'),
				'type'	=> 'left',
				'on'	=> array(),
			),
			array(
				'table'	=> array(call_user_func(array($this->model_to, 'table')), $alias_to),
				'type'	=> 'left',
				'on'	=> array(),
			),
		);

		reset($this->key_from);
		foreach ($this->key_through_from as $key)
		{
			$join[0]['on'][] = array($alias_from.'.'.current($this->key_from), '=', $alias_to.'_through.'.$key);
			next($this->key_from);
		}

		reset($this->key_to);
		foreach ($this->key_through_to as $key)
		{
			$join[1]['on'][] = array($alias_to.'_through.'.$key, '=', $alias_to.'.'.current($this->key_to));
			next($this->key_to);
		}

		return $join;
	}

	public function hydrate($row, &$select, $rel_name, $parent, &$parent_rels)
	{
		// simple key1, key2 table without - just delete the additional keys and return
		if (empty($this->model_through))
		{
			foreach ($select as $key => $s)
			{
				if (preg_match('/^t[0-9]+_through\\./uiD', $s[0]) > 0)
				{
					unset($select[$key]);
				}
			}
			return;
		}
		// turns out it's a relation through a model, create the model and relate this shit
		else
		{
			$obj = array();
			$rel = call_user_func(array($this->model_from, 'relations'), $this->model_through);
			foreach ($select as $key => $s)
			{
				if (preg_match('/^t[0-9]+_through\\.([a-z0-9_]+)/uiD', $s[0], $matches) > 0)
				{
					$obj[$matches[1]] = $row[$s[1]];
					unset($select[$key]);
				}
			}

			$pk = call_user_func(array($rel->model_to, 'implode_pk'), $obj);
			! array_key_exists($this->model_through, $parent_rels) and $parent_rels[$this->model_through] = array();
			if ( ! array_key_exists($pk, $parent_rels[$this->model_through]))
			{
				$obj = call_user_func(array($rel->model_to, 'factory'), $obj, false);
				$parent_rels[$this->model_through][$pk] = $obj;
			}

			$obj_rels = $obj->_relate();
			$parent_pk = call_user_func(array($parent, 'implode_pk'), $parent);
			! array_key_exists($rel_name, $obj_rels) and $obj_rels[$rel_name] = array();
			if ( ! array_key_exists($parent_pk, $obj_rels[$rel_name]))
			{
				$obj_rels[$rel_name][$parent_pk] = $parent;
				$obj->_relate($obj_rels);
			}
		}
	}
}

/* End of file manymany.php */