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
 * Validation object
 *
 * Object that performs the actual validation
 *
 * @package		Fuel
 * @subpackage	Core
 * @category	Core
 * @author		Jelmer Schreuder
 *
 * Notes:
 * - Needs a proper name, might become a driver but don't know why it would be yet.
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
			foreach ($this->set->get_callables() as $class)
			{
				$callable_rule = true;
				$this->rules[] = array(array($class, $callback), $args);
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