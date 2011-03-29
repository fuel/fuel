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

class Observer_Validation extends Observer {

	public static function get_fieldset($class)
	{
		$properties = $class::properties();

		if ($val = \Fieldset::instance($class))
		{
			return $val;
		}

		$val = \Fieldset::factory($class);
		foreach ($properties as $p => $settings)
		{
			if (empty($settings['validation']))
			{
				continue;
			}
			$field = $val->add($p, ! empty($settings['title']) ? $settings['title'] : $p);
			if ( ! emtpy($settings['rules']))
			{
				foreach ($settings['rules'] as $rule => $args)
				{
					call_user_func_array(array($field, 'add_rule'), $args);
				}
			}
		}

		return $val;
	}

	public function before_save(Model $obj)
	{
		$val = static::get_fieldset(get_class($obj))->validation();

		if ( ! $val->run($obj))
		{
			throw new ValidationFailed();
		}
	}
}

// Exception to throw when validation failed
class ValidationFailed extends Exception {}

// End of file validation.php