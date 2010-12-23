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

use Fuel\App as App;

// ------------------------------------------------------------------------

/**
 * Fieldset Class
 *
 * Define a set of fields that can be used to generate a form or to validate input.
 *
 * @package		Fuel
 * @category	Core
 * @author		Jelmer Schreuder
 */
class Fieldset_Field
{
	/**
	 * @var	Fieldset	Fieldset this field belongs to
	 */
	protected $fieldset;

	/**
	 * @var	string	Name of this field
	 */
	protected $name = '';

	/**
	 * @var	string	Field type for form generation
	 */
	protected $type = 'text';

	/**
	 * @var	string	Field label for validation errors and form label generation
	 */
	protected $label = '';

	/**
	 * @var	mixed	(Default) value of this field
	 */
	protected $value;

	/**
	 * @var	array	Rules for validation
	 */
	protected $rules = array();

	/**
	 * @var	array	Attributes for form generation
	 */
	protected $attributes = array();

	/**
	 * @var	array	Options, only available for select, radio & checkbox types
	 */
	protected $options = array();

	/**
	 * @var	string	Template for form building
	 */
	protected $template;

	/**
	 * Constructor
	 *
	 * @param 	string
	 * @param	string
	 * @param	array
	 * @param	array
	 * @param	Fieldset
	 */
	public function __construct($name, $label = '', Array $attributes = array(), Array $rules = array(), App\Fieldset $fieldset)
	{
		$this->name = (string) $name;
		$this->fieldset = $fieldset;

		foreach (array('label', 'type', 'value', 'options') as $prop)
		{
			if (array_key_exists($prop, $attributes))
			{
				$this->{'set_'.$prop}($attributes[$prop]);
				unset($attributes[$prop]);
			}
		}
		$this->attributes = array_merge($this->attributes, $attributes);

		// only when non-empty, will overwrite what was given in $name
		$label && $this->set_label($label);

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
		$this->label = (string) $label;
		$this->set_attribute('label', $label);
	}

	/**
	 * Change the field type for form generation
	 *
	 * @param	string
	 */
	public function set_type($type)
	{
		$this->type = (string) $type;
		$this->set_attribute('type', $type);
	}

	/**
	 * Change the field's current or default value
	 *
	 * @param	string
	 */
	public function set_value($value)
	{
		$this->value = $value;
		$this->set_attribute('value', $value);
	}

	/**
	 * Template the output
	 *
	 * @param	string
	 */
	public function set_template($template = null)
	{
		$this->template = $template;
	}

	/**
	 * Add a validation rule
	 * any further arguements after the callback will be used as arguements for the callback
	 *
	 * @param	string|Callback	either a validation rule or full callback
	 * @return	Fieldset_Field	this, to allow chaining
	 */
	public function add_rule($callback)
	{
		$args = array_slice(func_get_args(), 1);

		// Rules are validated and only accepted when given as an array consisting of
		// array(callback, params) or just callbacks in an array.
		$callable_rule = false;
		if (is_string($callback))
		{
			$callback_method = '_validation_'.$callback;
			$callables = $this->fieldset->validation()->callables();
			foreach ($callables as $callback_class)
			{
				if (method_exists($callback_class, $callback_method))
				{
					$callable_rule = true;
					$this->rules[] = array(array($callback_class, $callback_method), $args);
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
				$string = ! is_array($callback)
						? $callback
						: is_object(@$callback[0])
							? get_class(@$callback[0]).'->'.@$callback[1]
							: @$callback[0].'::'.@$callback[1];
				Error::notice('Invalid rule "'.$string.'" passed to Validation, not used.');
			}
		}

		return $this;
	}

	/**
	 * Sets an attribute on the field
	 *
	 * @param	string
	 * @param	mixed			new value or null to unset
	 * @return	Fieldset_Field	this, to allow chaining
	 */
	public function set_attribute($config, $value)
	{
		$config = is_array($config) ? $config : array($config => $value);
		foreach ($config as $key => $value)
		{
			if ($value === null)
			{
				unset($this->attributes[$key]);
			}
			else
			{
				$this->attributes[$key] = $value;
			}
		}

		return $this;
	}

	/**
	 * Get a single or multiple attributes by key
	 *
	 * @param	string|array	a single key or multiple in an array, empty to fetch all
	 * @param	mixed			default output when attribute wasn't set
	 * @return	mixed|array		a single attribute or multiple in an array when $key input was an array
	 */
	public function get_attribute($key = null, $default = null)
	{
		if ($key === null)
		{
			return $this->attributes;
		}

		if (is_array($key))
		{
			$output = array();
			foreach ($key as $k)
			{
				$output[$k] = array_key_exists($k, $this->attributes) ? $this->attributes[$k] : $default;
			}
			return $output;
		}

		return array_key_exists($key, $this->attributes) ? $this->attributes[$key] : $default;
	}

	/**
	 * Add an option value with label
	 *
	 * @param	string|array	one option value, or multiple value=>label pairs in an array
	 * @param	string
	 * @return	Fieldset_Field	this, to allow chaining
	 */
	public function set_options($value, $label = null)
	{
		$value = is_array($value) ? $value : array($value => $label);
		foreach ($value as $key => $label)
		{
			$key = is_int($key) ? $label : $key;
			$this->options[(string) $key] = (string) $label;
		}

		return $this;
	}

	/**
	 * Get the options available for this field
	 *
	 * @return	array
	 */
	public function options()
	{
		return $this->options;
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

	/**
	 * Alias for $this->fieldset->add() to allow chaining
	 */
	public function add($name, $label = '', array $attributes = array(), array $rules = array())
	{
		return $this->fieldset->add($name, $label, $attributes, $rules);
	}

	/**
	 * Alias for $this->fieldset->form()->build_field() for this field
	 */
	public function build()
	{
		return $this->fieldset->form()->build_field($this);
	}

	/**
	 * Alias for $this->fieldset->validation->input() for this field
	 */
	public function input()
	{
		return $this->fieldset->validation()->input($this->name);
	}

	/**
	 * Alias for $this->fieldset->validation->validated() for this field
	 */
	public function validated()
	{
		return $this->fieldset->validation->validated($this->name);
	}

	/**
	 * Alias for $this->fieldset->validation->error() for this field
	 */
	public function error()
	{
		return $this->fieldset->validation()->error($this->name);
	}
}

/* End of file field.php */