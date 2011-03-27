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

class HasOne extends Relation {

	protected $singular = true;

	public function __construct($from, $name, array $config)
	{
		$this->name        = $name;
		$this->model_from  = $from;
		$this->model_to    = array_key_exists('model_to', $config) ? $config['model_to'] : 'Model_'.\Inflector::classify($name);
		$this->key_from    = array_key_exists('key_from', $config) ? (array) $config['key_from'] : $this->key_from;
		$this->key_to      = array_key_exists('key_to', $config) ? (array) $config['key_to'] : (array) \Inflector::foreign_key($this->model_from);

		$this->cascade_save    = array_key_exists('cascade_save', $config) ? $config['cascade_save'] : $this->cascade_save;
		$this->cascade_delete  = array_key_exists('cascade_save', $config) ? $config['cascade_save'] : $this->cascade_delete;
	}

	public function get(Model $from)
	{
		$query = call_user_func(array($this->model_to, 'find'));
		reset($this->key_to);
		foreach ($this->key_from as $key)
		{
			$query->where(current($this->key_to), $from->{$key});
			next($this->key_to);
		}
		return $query->get_one();
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
		return $properties;
	}

	public function join($alias_from, $rel_name, $alias_to_nr)
	{
		$alias_to = 't'.$alias_to_nr;
		$model = array(
			'model'      => $this->model_to,
			'table'      => array(call_user_func(array($this->model_to, 'table')), $alias_to),
			'join_type'  => 'left',
			'join_on'    => array(),
			'columns'    => $this->select($alias_to),
			'rel_name'   => $rel_name,
			'relation'   => $this
		);

		reset($this->key_to);
		foreach ($this->key_from as $key)
		{
			$model['join_on'][] = array($alias_from.'.'.$key, '=', $alias_to.'.'.current($this->key_to));
			next($this->key_to);
		}

		return array($model);
	}

	public function save($model_from, $model_to, $original_model_id, $parent_saved, $cascade)
	{
		if ( ! $parent_saved)
		{
			return;
		}

		$current_model_id = $model_to ? $model_to->implode_pk($model_to) : null;
		// Check if there was another model assigned (this supersedes any change to the foreign key(s))
		if ($current_model_id != $original_model_id)
		{
			// assign this object to the new objects foreign keys
			if ( ! empty($model_to))
			{
				reset($this->key_to);
				$frozen = $model_to->frozen(); // only unfreeze/refreeze when it was frozen
				$frozen and $model_to->unfreeze();
				foreach ($this->key_from as $pk)
				{
					$model_to->{current($this->key_to)} = $model_from->{$pk};
					next($this->key_to);
				}
				$frozen and $model_to->freeze();
			}
			// if still loaded set this object's old relation's foreign keys to null
			if ($obj = call_user_func(array($this->model_to, 'cached_object'), $original_model_id))
			{
				// check whether the object still refers to this model_from
				$changed = false;
				reset($this->key_to);
				foreach ($this->key_from as $pk)
				{
					if ($model_to->{current($this->key_to)} != $model_from->{$pk})
					{
						$changed = true;
					}
					next($this->key_to);
				}

				// when it still refers to this object, reset the foreign key(s)
				if ( ! $changed)
				{
					$frozen = $obj->frozen(); // only unfreeze/refreeze when it was frozen
					$frozen and $obj->unfreeze();
					foreach ($this->key_to as $fk)
					{
						$obj->{$fk} = null;
					}
					$frozen and $obj->freeze();
				}
			}
		}
		// if not check the model_to's foreign_keys
		else
		{
			// check if model_to still refers to this model_from
			$changed = false;
			reset($this->key_to);
			foreach ($this->key_from as $pk)
			{
				if ($model_to->{curren($this->key_to)} != $model_from->{$pk})
				{
					$changed = true;
				}
				next($this->key_to);
			}

			// if any of the keys changed, the relationship was broken - remove model_to from loaded objects
			if ($changed)
			{
				// Add the new relation to the model_from
				$model_from->unfreeze();
				$rel = $model_from->_relate();
				$rel[$this->name] = null;
				$model_from->_relate($rel);
				$model_from->freeze();
			}
		}

		$cascade = is_null($cascade) ? $this->cascade_save : (bool) $cascade;
		if ($cascade and ! empty($model_to))
		{
			$model_to->save();
		}
	}

	public function delete($model_from, $model_to, $parent_deleted, $cascade)
	{
		if ( ! $parent_deleted)
		{
			return;
		}

		$cascade = is_null($cascade) ? $this->cascade_save : (bool) $cascade;
		if ($cascade)
		{
			$model_to->delete();
		}
	}
}

/* End of file hasone.php */