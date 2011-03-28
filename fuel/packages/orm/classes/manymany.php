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
		$this->name        = $name;
		$this->model_from  = $from;
		$this->model_to    = array_key_exists('model_to', $config) ? $config['model_to'] : 'Model_'.\Inflector::classify($name);
		$this->key_from    = array_key_exists('key_from', $config) ? (array) $config['key_from'] : $this->key_from;
		$this->key_to      = array_key_exists('key_to', $config) ? (array) $config['key_to'] : $this->key_to;

		if ( ! empty($config['table_through']))
		{
			$this->table_through = $config['table_through'];
		}
		else
		{
			$table_name = array($this->model_from, $this->model_to);
			natcasesort($table_name);
			$table_name = array_merge($table_name);
			$this->table_through = \Inflector::tableize($table_name[0]).'_'.\Inflector::tableize($table_name[1]);
		}
		$this->key_through_from = ! empty($config['key_through_from'])
			? (array) $config['key_through_from'] : (array) \Inflector::foreign_key($this->model_from);
		$this->key_through_to = ! empty($config['key_through_to'])
			? (array) $config['key_through_to'] : (array) \Inflector::foreign_key($this->model_to);

		$this->cascade_save    = array_key_exists('cascade_save', $config) ? $config['cascade_save'] : $this->cascade_save;
		$this->cascade_delete  = array_key_exists('cascade_save', $config) ? $config['cascade_save'] : $this->cascade_delete;
	}

	public function get(Model $from)
	{
	}

	public function select_through($table)
	{
		foreach ($this->key_through_to as $to)
		{
			$properties[] = $table.'.'.$to;
		}
		foreach ($this->key_through_from as $from)
		{
			$properties[] = $table.'.'.$from;
		}

		return $properties;
	}

	public function join($alias_from, $rel_name, $alias_to_nr)
	{
		$alias_to = 't'.$alias_to_nr;

		$models = array(
			array(
				'model'      => null,
				'table'      => array($this->table_through, $alias_to.'_through'),
				'join_type'  => 'left',
				'join_on'    => array(),
				'columns'    => $this->select_through($alias_to.'_through'),
				'rel_name'   => $this->model_through,
				'relation'   => $this
			),
			array(
				'model'      => $this->model_to,
				'table'      => array(call_user_func(array($this->model_to, 'table')), $alias_to),
				'join_type'  => 'left',
				'join_on'    => array(),
				'columns'    => $this->select($alias_to),
				'rel_name'   => $rel_name,
				'relation'   => $this
			)
		);

		reset($this->key_from);
		foreach ($this->key_through_from as $key)
		{
			$models[0]['join_on'][] = array($alias_from.'.'.current($this->key_from), '=', $alias_to.'_through.'.$key);
			next($this->key_from);
		}

		reset($this->key_to);
		foreach ($this->key_through_to as $key)
		{
			$models[1]['join_on'][] = array($alias_to.'_through.'.$key, '=', $alias_to.'.'.current($this->key_to));
			next($this->key_to);
		}

		return $models;
	}

	public function save($model_from, $models_to, $original_model_ids, $parent_saved, $cascade)
	{
		if ( ! $parent_saved)
		{
			return;
		}

		if ( ! is_array($models_to) and ($models_to = is_null($models_to) ? array() : $models_to) !== array())
		{
			throw new Exception('Assigned relationships must be an array or null, given relationship value for '.
				$this->name.' is invalid.');
		}
		$original_model_ids === null and $original_model_ids = array();

		foreach ($models_to as $key => $model_to)
		{
			if ( ! $model_to instanceof $this->model_to)
			{
				throw new Exception('Invalid Model instance added to relations in this model.');
			}

			$current_model_id = $model_to ? $model_to->implode_pk($model_to) : null;
				// unset current model from from array
				unset($original_model_ids[array_search($current_model_id, $original_model_ids)]);

			// Check if the model was already assigned, if not INSERT relationships:
			if ( ! in_array($current_model_id, $original_model_ids))
			{
				$ids = array();
				reset($this->key_from);
				foreach ($this->key_through_from as $key)
				{
					$ids[$key] = $model_from->{current($this->key_from)};
					next($this->key_from);
				}

				reset($this->key_to);
				foreach ($this->key_through_to as $key)
				{
					$ids[$key] = $model_to->{current($this->key_to)};
					next($this->key_to);
				}

				\DB::insert($this->table_through)->set($ids)->execute();
			}

			// ensure correct pk assignment
			if ($key != $current_model_id)
			{
				$model_from->unfreeze();
				$rel = $model_from->_relate();
				if ($rel[$this->name][$key] === $model_to)
				{
					unset($rel[$this->name][$key]);
				}
				$rel[$this->name][$current_model_id] = $model_to;
				$model_from->_relate($rel);
				$model_from->freeze();
			}
		}

		// If any original ids are left they are no longer assigned, DELETE the relationships:
		foreach ($original_model_ids as $original_model_id)
		{
			$query = \DB::delete($this->table_through);

			reset($this->key_from);
			foreach ($this->key_through_from as $key)
			{
				$query->where($key, '=', $model_from->{current($this->key_from)});
				next($this->key_from);
			}

			$to_keys = count($this->key_to) == 1 ? array($original_model_id) : explode('][', substr($original_model_id, 1, -1));
			reset($to_keys);
			foreach ($this->key_through_to as $key)
			{
				$query->where($key, '=', current($to_keys));
				next($to_keys);
			}

			$query->execute();
		}

		$cascade = is_null($cascade) ? $this->cascade_save : (bool) $cascade;
		if ($cascade and ! empty($models_to))
		{
			foreach ($models_to as $m)
			{
				$m->save();
			}
		}
	}

	public function delete($model_from, $models_to, $parent_deleted, $cascade)
	{
		if ( ! $parent_deleted)
		{
			return;
		}

		// Delete all relationship entries for the model_from
		$query = \DB::delete($this->table_through);
		reset($this->key_from);
		foreach ($this->key_through_from as $key)
		{
			$query->where($key, '=', $model_from->{current($this->key_from)});
			next($this->key_from);
		}
		$query->delete();

		$cascade = is_null($cascade) ? $this->cascade_save : (bool) $cascade;
		if ($cascade and ! empty($model_to))
		{
			foreach ($models_to as $m)
			{
				$m->delete();
			}
		}
	}
}

/* End of file manymany.php */