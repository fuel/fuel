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
		$this->model_from  = $from;
		$this->model_to    = array_key_exists('model_to', $config) ? $config['model_to'] : 'Model_'.\Inflector::classify($name);
		$this->key_from    = array_key_exists('key_from', $config) ? (array) $config['key_from'] : $this->key_from;
		$this->key_to      = array_key_exists('key_to', $config) ? (array) $config['key_to'] : (array) \Inflector::foreign_key($this->model_from);
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

	public function join($alias)
	{
		$join = array(
			'table'	=> array(call_user_func(array($this->model_to, 'table')), $alias),
			'type'	=> 'left',
			'on'	=> array(),
		);

		reset($this->key_to);
		foreach ($this->key_from as $key)
		{
			$join['on'][] = array('t0.'.$key, '=', $alias.'.'.current($this->key_to));
			next($this->key_to);
		}

		return $join;
	}
}

/* End of file hasone.php */