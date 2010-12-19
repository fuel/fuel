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

use Fuel\App as App;
use Fuel\App\DB;

class BelongsTo extends Association {

	public function __construct(&$source, $dest, $options = null)
	{
		parent::__construct($source, $dest, $options);
		$this->foreign_key = App\Inflector::foreign_key($this->dest_class);
	}

	public function set($value, &$source)
	{
		if ($value instanceof $this->dest_class)
		{
			if ( ! $value->is_new_record())
			{
				$source->{$this->foreign_key} = $value->{$value->get_primary_key()};
			}
			else
			{
				$source->{$this->foreign_key} = null;
			}
			$this->value = $value;
		}
		else
		{
			throw new App\Exception("Did not get expected class: {$this->dest_class}", Exception::UnexpectedClass);
		}
	}

	public function get(&$source, $force=false)
	{
		if ($this->value instanceof $this->dest_class && !$force)
		{
			return $this->value;
		}
		else
		{
			$this->value = call_user_func_array(
							array($this->dest_class, 'find'),
							array($source->{$this->foreign_key}));
			return $this->value;
		}
	}

	public function join()
	{
		$dest_table = App\Inflector::tableize($this->dest_class);
		$source_table = App\Inflector::tableize($this->source_class);
		$dest_inst = new $this->dest_class;
		$columns = $dest_inst->get_columns();

		$join = array(
			'table'	=> $dest_table,
			'type'	=> 'LEFT OUTER',
			'on'	=> array($source_table.'.'.$this->foreign_key, '=', $dest_table.'.'.$dest_inst->get_primary_key())
		);

		return array(array($dest_table => $columns), $join);
	}

	public function populate_from_find($attributes)
	{
		// check if all attributes are NULL
		$uniq_vals = array_unique(array_values($attributes));
		if (count($uniq_vals) == 1 && is_null(current($uniq_vals)))
		{
			return;
		}

		$class = $this->dest_class;
		$item = new $class($attributes);
		$item->new_record = false;
		$this->value = $item;
	}

}

/* End of file belongsto.php */
