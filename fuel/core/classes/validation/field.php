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

namespace Fuel\Core;
use Fuel\App;

// ------------------------------------------------------------------------

/**
 * Validation field
 *
 * Describes a field and which rules it has for validating.
 *
 * @package		Fuel
 * @subpackage	Core
 * @category	Core
 * @author		Jelmer Schreuder
 */
class Validation_Field {

	/**
	 * @var	string	keyname in the post or given values
	 */
	protected $key = '';

	/**
	 * @var	Validation_Set	the set this field belongs to
	 */
	protected $set = null;

	/**
	 * @var	string	label for this field
	 */
	protected $label = '';

	/**
	 * @var	array	rules for validating this field
	 */
	protected $rules = array();

	public function __construct($key, $label = '', Array $rules = array(), $validation_set = null)
	{
		$this->set = $validation_set ?: Validation::instance();
		$this->key = $key;
		$this->label = $label;

		foreach ($rules as $rule)
		{
			call_user_func_array(array($this, 'add_rule'), $rule);
		}
	}

	/**
	 * Change the field label
	 *
	 * @param	string
	 */
	public function set_label($label)
	{
		$this->label = $label;
	}

	/**
	 * Add a validation rule
	 * any further arguements after the callback will be used as arguements for the callback
	 *
	 * @param	$function
	 * @return	void
	 */
	public function add_rule($callback)
	{
		$args = array_slice(func_get_args(), 1);

		// Rules are validated and only accepted when given as an array consisting of
		// array(callback, params) or just callbacks in an array.
		$callable_rule = false;
		if (is_string($callback))
		{
			$callback_full = '_validation_'.$callback;
			foreach ($this->set->get_callables() as $class)
			{
				if (method_exists($class, $callback_full))
				{
					$callable_rule = true;
					$this->rules[] = array(array($class, $callback_full), $args);
				}
			}
		}

		// when no callable function was found, try regular callbacks
		if ( ! $callable_rule)
		{
			if (is_callable($callback))
			{
				$this->rules = array($callback, $args);
			}
			else
			{
				Error::notice('Invalid rule passed to Validation, not used.');
			}
		}

		return $this;
	}

	/**
	 * Configuring this field is done, returns the set to allow you to add another field
	 *
	 * @return	Validation_Set
	 */
	public function end()
	{
		return $this->set;
	}

	/**
	 * This allows for chaining without needing end()
	 *
	 * @param	string
	 * @param	string
	 * @param	array
	 * @return	Validation_Field
	 */
	public function add_field($field, $label = '', Array $rules = array())
	{
		return $this->set->add_field($field, $label, $rules);
	}

	/**
	 * Magic get method to allow getting class properties but still having them protected
	 * to disallow writing.
	 *
	 * @return	mixed
	 */
	public function __get($property)
	{
		return $this->$property;
	}
}

/* End of file field.php */