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

namespace ActiveRecord;


use \DB;

class Association {

	protected $dest_class;
	protected $source_class;
	protected $value;
	protected $options;

	public function __construct($source, $dest, $options = null)
	{
		$this->source_class = get_class($source);

		if (isset($options['class_name']))
		{
			$this->dest_class = $options['class_name'];
		}
		else
		{
			$this->dest_class = \Inflector::classify($dest);
		}

		if (isset($options['foreign_key']))
		{
			$this->foreign_key = $options['foreign_key'];
		}
		else
		{
			$this->foreign_key = \Inflector::foreign_key($this->source_class);
		}

		$namespace = ucfirst(\Request::active()->module).'\\';
		if (class_exists($dest = $namespace.'Model_'.$this->dest_class))
		{
			$this->dest_class = $dest;
		}

		if ( ! class_exists($this->source_class))
		{
			$this->source_class = 'Model_'.$this->source_class;
		}

		$this->options = $options;
	}

	public function needs_saving()
	{
		if ( ! $this->value instanceof $this->dest_class)
		{
			return false;
		}
		else
		{
			return $this->value->is_new_record() || $this->value->is_modified();
		}
	}

	public function delete(&$source)
	{
		if (isset($this->options['dependent']) && $this->options['dependent'] == 'destroy')
		{
			$this->get($source);
			if (is_array($this->value))
			{
				foreach ($this->value as $val)
				{
					$val->destroy();
				}
			}
			else
			{
				$this->value->destroy();
			}
		}
	}

}

/* End of file association.php */