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

namespace Fuel;

class Validation extends Singleton {

	public static function factory()
	{
		return new Validation_Object();
	}

	final private function __construct() {}

	public static function add_field($field, $title = null, Array $rules = array())
	{
		return static::instance()->add_field($field, $title, $rules);
	}

	public static function add_model($model)
	{
		return static::instance()->add_model($model);
	}

	public static function add_callable($class)
	{
		return static::instance()->add_callable($class);
	}

	public static function run($input = null)
	{
		return static::instance()->run($input);
	}

	public static function validated($field = false, $default = false)
	{
		return static::instance()->validated($field, $default);
	}

	public static function errors($field = false, $default = false)
	{
		return static::instance()->errors($field, $default);
	}
}

// This needs to get a proper name and separate file, just in te same file while outlining the Validation class
class Validation_Object {

	/**
	 * @var array consists of fieldnames, field titles & rules to be used on them
	 */
	protected $fields = array();

	/**
	 * @var array consists of objects and classnames that don't need a full callback passed
	 */
	protected $callables = array();

	/**
	 * @var array consists of the fieldnames and their values when validation succeeded for that field
	 */
	protected $output = array();

	/**
	 * @var array consists of errors given during validation
	 */
	protected $errors = array();

	/**
	 * Prevent instance to be created outside of Validation
	 */
	public function __construct()
	{
		$this->callables = array($this);
	}

	/**
	 * Add field to be validated with title and rules
	 *
	 * @param	string	field variable name
	 * @param	string	field title
	 * @param	array	consisting of rules, which are valid callbacks or array(callback, params array)
	 */
	public function add_field($field, $title = null, Array $rules = array())
	{
		// Allow for passing multiple rules at once
		if (is_array($field))
		{
			foreach($field as $rule)
			{
				$this->add_rule($rule[0], $rule[1], $rule[2]);
			}
			return;
		}

		$this->fields[$field] = array(
			'field'	=> $field,
			'title'	=> $title,
			'rules'	=> array()
		);

		// Rules are validated and only accepted when given as an array consisting of
		// array(callback, params) or just callbacks in an array.
		foreach ($rules as $r)
		{
			if (is_array($r) && (empty($r[1]) || is_array($r[1])) && is_callable($r[0]))
			{
				$this->fields[$field]['rules'][] = $r;
			}
			elseif (is_callable($r))
			{
				$this->fields[$field]['rules'][] = array($r, array());
			}
			else
			{
				// try on callable objects
				$callable_rule = false;
				if (is_string($r) || (is_array($r) && (empty($r[1]) || is_array($r[1]))))
				{
					foreach ($this->callables as $class)
					{
						if (is_array($r) || is_callable(array($class, $r[0])))
						{
							$callable_rule = true;
							$this->fields[$field]['rules'][] = array(array($class, $r[0]), $r[1]);
						}
						elseif (is_string($r) && is_callable(array($class, $r)))
						{
							$callable_rule = true;
							$this->fields[$field]['rules'][] = array(array($class, $r), array());
						}
					}
				}

				// not found, give a notice but don't break
				if ( ! $callable_rule)
				{
					trigger_error('Invalid rule passed to Validation, not used.', E_USER_WARNING);
				}
			}
		}
	}

	public function add_model($model)
	{
		if ( ! is_callable(array($model, '_fuel_validation')))
		{
			throw new Fuel_Exception('Invalid model or no _fuel_validation() method to return fields.');
		}

		$this->fields = array_merge($model->_fuel_validation(), $this->fields);
		$this->add_callable($model);
	}

	public function add_callable($class)
	{
		if ( ! (is_object($class) || class_exists($class)))
		{
			throw new Fuel_Exception('Input for add_callable is not a valid object or class.');
		}
		
		$this->callables[] = $class;
	}

	public function run($input = null)
	{
		$this->output = array();
		$this->errors = array();
		foreach($this->fields as $field => $settings)
		{
			$value = is_null($input) ? $_POST[$field] : @$input[$field];
			try
			{
				foreach ($settings['rules'] as $rule)
				{
					$callback	= $rule[0];
					$params		= (array) @$rule[1];
					$this->_run_rule($rule, $value, $params, $settings);
					$this->output[$field] = $value;
				}
			}
			catch (Validation_Rule_Exception $v)
			{
				$this->errors[$field] = $v;
			}
		}

		return empty($this->errors);
	}

	protected function _run_rule($rule, &$value, $params, $field)
	{
		$output = call_user_func_array($rule, array_merge(array($value), $params));

		if ($output === false && $value !== false)
		{
			throw new Validation_Rule_Exception($field, $value, $rule, $params);
		}
		elseif ($output !== true)
		{
			$value = $output;
		}
	}

	public function validated($field = false, $default = false)
	{
		if ($field === false)
		{
			return $this->output;
		}

		return array_key_exists($field, $this->output) ? $this->output[$field] : $default;
	}

	public function errors($field = false, $default = false)
	{
		if ($field === false)
		{
			return $this->errors;
		}

		return array_key_exists($field, $this->errors) ? $this->errors[$field] : $default;
	}
}

class Validation_Rule_Exception extends Exception {
	public $field = '';
	public $value = '';
	public $callback = '';
	public $params = array();

	public function __construct($field, $value, $callback, $params)
	{
		$this->field = $field;
		$this->value = $value;
		$this->params = $params;

		$callback = is_string($callback) ? strstr($callback, array('::', ':')) : get_class($callback[0]).':'.$callback[1];
	}

	public function get_message($msg = false)
	{
		$msg = is_null($msg) ? Lang::line('validation.'.$this->callback) : $msg;
		if ($msg == false)
		{
			return 'Validation rule '.$this->callback.' failed for '.$this->field['field'];
		}

		$replace = array(
			'name'	=> $this->field['name'],
			'title'	=> $this->field['title']
		);
		foreach($this->fields as $key => $val)
		{
			$replace['field:'.$key] = $val;
		}
		foreach($this->params as $key => $val)
		{
			$replace['param:'.$key] = $val;
		}

		return Lang::line('validation.'.$this->callback, $replace);
	}

	public function __toString()
	{
		return $this->get_message();
	}
}

/* End of file validation.php */