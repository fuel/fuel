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

	/**
	 * Set a Model's properties as fields on a Fieldset, which will be created with the Model's
	 * classname if none is provided.
	 *
	 * @param   string
	 * @param   Fieldset|null
	 * @return  Fieldset
	 */
	public static function set_fields($class, $fieldset = null)
	{
		$properties = $class::properties();

		if (is_null($fieldset))
		{
			$fieldset = \Fieldset::instance($class);
			if ( ! $fieldset)
			{
				$fieldset = \Fieldset::factory($class);
			}
		}

		foreach ($properties as $p => $settings)
		{
			if (empty($settings['validation']))
			{
				continue;
			}
			$field = $fieldset->add($p, ! empty($settings['label']) ? $settings['label'] : $p);
			if ( ! empty($settings['rules']))
			{
				foreach ($settings['rules'] as $rule => $args)
				{
					array_unshift($args, $rule);
					call_user_func_array(array($field, 'add_rule'), $args);
				}
			}
		}

		return $fieldset;
	}

	/**
	 * Execute before saving the Model
	 *
	 * @param   Model
	 * @throws  ValidationFailed
	 */
	public function before_save(Model $obj)
	{
		$val = static::set_fields(get_class($obj))->validation();

		if ($val->run($obj) === false)
		{
			throw new ValidationFailed();
		}
	}
}

// Exception to throw when validation failed
class ValidationFailed extends Exception {}

// End of file validation.php