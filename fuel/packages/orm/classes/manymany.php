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
		else
		{
			$table_name = array($this->model_from, $this->model_to);
			natcasesort($table_name);
			$this->table_through = array_key_exists('table', $config['through'])
				? \Inflector::tableize($table_name[0]).'_'.\Inflector::tableize($table_name[1])
				: $config['through']['table'];
		}
		$this->key_through_from = array_key_exists('key_from', $config['through'])
			? (array) $config['through']['key_from'] : (array) \Inflector::foreign_key($this->model_from);
		$this->key_through_to = array_key_exists('key_to', $config['through'])
			? (array) $config['through']['key_to'] : (array) \Inflector::foreign_key($this->model_to);
	}

	public function get(Model $from)
	{
	}

	public function select($table)
	{
	}

	public function join($alias)
	{
	}
}

/* End of file manymany.php */