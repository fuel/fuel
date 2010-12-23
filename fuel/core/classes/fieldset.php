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
class Fieldset
{
	/**
	 * @var	Fieldset
	 */
	protected static $_instance;

	/**
	 * @var	array	contains references to all instantiations of Fieldset
	 */
	protected static $_instances = array();

	public static function factory($name = '', Array $config = array())
	{
		$name = $name ?: 'default';

		if ($exists = static::instance($name))
		{
			App\Error::notice('Fieldset with this name exists already, cannot be overwritten.');
			return $exists;
		}

		static::$_instances[$name] = new Fieldset($name, $config);

		return static::$_instances[$name];
	}

	/**
	 * Return a specific instance, or the default instance (is created if necessary)
	 *
	 * @param	string	driver id
	 * @return	Auth_Login_Driver
	 */
	public static function instance($instance = null)
	{
		if ($instance !== null)
		{
			if ( ! array_key_exists($instance, static::$_instances))
			{
				return false;
			}

			return static::$_instances[$instance];
		}

		if (static::$_instance === null)
		{
			static::$_instance = static::factory();
		}

		return static::$_instance;
	}

	/**
	 * @var	string	instance id
	 */
	protected $name;

	/**
	 * @var	array	array of Fieldset_Field objects
	 */
	protected $fields = array();

	/**
	 * @var	Validation	instance of validation
	 */
	protected $validation;

	/**
	 * @var	Form	instance of form
	 */
	protected $form;

	/**
	 * @var	array	configuration array
	 */
	protected $config = array();

	/**
	 * Class constructor
	 *
	 * @param	string
	 * @param	array
	 */
	protected function __construct($name, Array $config = array())
	{
		$this->name = (string) $name;
		$this->config = $config;
	}

	/**
	 * Get related Validation instance or create it
	 *
	 * @return	Validation
	 */
	public function validation()
	{
		if (empty($this->validation))
		{
			$this->validation = Validation::factory($this->name, $this);
		}

		return $this->validation;
	}

	/**
	 * Get related Form instance or create it
	 *
	 * @return	Form
	 */
	public function form()
	{
		if (empty($this->form))
		{
			$this->form = Form::factory($this->name, $this);
		}

		return $this->form;
	}

	/**
	 * Factory for Fieldset_Field objects
	 *
	 * @param	string
	 * @param	string
	 * @param	array
	 * @param	array
	 * @return	Fieldset_Field
	 */
	public function add($name, $label = '', array $attributes = array(), array $rules = array())
	{
		if ($field = static::field($name))
		{
			App\Error::notice('Field with this name exists already, cannot be overwritten through add().');
			return $field;
		}

		$field = new Fieldset_Field($name, $label, $attributes, $rules, $this);
		$this->fields[$name] = $field;

		return $field;
	}

	/**
	 * Get Field instance
	 *
	 * @param	string					null to fetch an array of all
	 * @return	Fieldset_Field|false	returns false when field wasn't found
	 */
	public function field($name = null)
	{
		if ($name === null)
		{
			return $this->fields;
		}

		if ( ! array_key_exists($name, $this->fields))
		{
			return false;
		}

		return $this->fields[$name];
	}

	/**
	 * Add a model's fields
	 * The model must have a method "set_form_fields" that takes this Fieldset instance
	 * and adds fields to it.
	 *
	 * @param	string|Object	either a full classname (including full namespace) or object instance
	 * @param	array|Object	array or object that has the exactly same named properties to populate the fields
	 * @param	string			method name to call on model for field fetching
	 * @return	Fieldset		this, to allow chaining
	 */
	public function add_model($class, $instance = null, $method = 'set_form_fields')
	{
		if ((is_string($class) && is_callable($callback = array('Fuel\\App\\'.$class, $method)))
			|| is_callable($callback = array($class, $method)))
		{
			$instance ? call_user_func($callback, $this, $instance) : call_user_func($callback, $this);
		}

		return $this;
	}

	/**
	 * Sets a config value on the fieldset
	 *
	 * @param	string
	 * @param	mixed
	 * @return	Fieldset	this, to allow chaining
	 */
	public function set_config($config, $value = null)
	{
		$config = is_array($config) ? $config : array($config => $value);
		foreach ($config as $key => $value)
		{
			$this->config[$key] = $value;
		}

		return $this;
	}

	/**
	 * Get a single or multiple config values by key
	 *
	 * @param	string|array	a single key or multiple in an array, empty to fetch all
	 * @param	mixed			default output when config wasn't set
	 * @return	mixed|array		a single config value or multiple in an array when $key input was an array
	 */
	public function get_config($key = null, $default = null)
	{
		if ($key === null)
		{
			return $this->config;
		}

		if (is_array($key))
		{
			$output = array();
			foreach ($key as $k)
			{
				$output[$k] = array_key_exists($k, $this->config) ? $this->config[$k] : $default;
			}
			return $output;
		}

		return array_key_exists($key, $this->config) ? $this->config[$key] : $default;
	}

	/**
	 * Set all fields to the given and/or posted input
	 *
	 * @return Fieldset	this, to allow chaining
	 */
	public function repopulate()
	{
		foreach ($this->fields as $f)
		{
			if (($value = $this->input($f->name, null)) !== null)
			$f->set_value($value);
		}

		return $this;
	}

	/**
	 * Magic method toString that will build this as a form
	 *
	 * @return	string
	 */
	public function __toString()
	{
		return $this->build();
	}

	/**
	 * Alias for $this->form()->build() for this fieldset
	 */
	public function build()
	{
		return $this->form()->build();
	}

	/**
	 * Alias for $this->validation()->input()
	 */
	public function input($field = null)
	{
		return $this->validation()->input($field);
	}

	/**
	 * Alias for $this->validation()->validated()
	 */
	public function validated($field = null)
	{
		return $this->validation->validated($field);
	}

	/**
	 * Alias for $this->validation()->error()
	 */
	public function error($field = null)
	{
		return $this->validation()->error($field);
	}

	/**
	 * Alias for $this->validation()->show_errors()
	 */
	public function show_errors(Array $config = array())
	{
		return $this->validation()->show_errors($config);
	}
}

/* End of file fieldset.php */