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
			$this->dest_class = App\Inflector::classify($dest);
		}

		if (isset($options['foreign_key']))
		{
			$this->foreign_key = $options['foreign_key'];
		}
		else
		{
			$this->foreign_key = App\Inflector::foreign_key($this->source_class);
		}

		if ( ! class_exists($this->dest_class))
		{
			$this->dest_class = 'Fuel\\App\\Model\\'.$this->dest_class;
		}

		if ( ! class_exists($this->source_class))
		{
			$this->source_class = 'Fuel\\App\\Model\\'.$this->source_class;
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

	public function destroy(&$source)
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