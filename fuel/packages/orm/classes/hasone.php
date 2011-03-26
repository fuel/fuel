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

	public function save($model_from, $model_to, $original_model_to, $parent_saved, $cascade)
	{
		if ( ! $parent_saved)
		{
			return;
		}

		$cascade = is_null($cascade) ? $this->cascade_save : (bool) $cascade;
		if ($cascade)
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