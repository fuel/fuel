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
class Validation_Object {

	/**
	 * @var	array	consists of fieldnames, field titles & rules to be used on them
	 */
	protected $fields = array();

	/**
	 * @var	array	consists of objects and classnames that don't need a full callback passed
	 */
	protected $callables = array();

	/**
	 * @var	array	consists of the fieldnames and their values when validation succeeded for that field
	 */
	protected $output = array();

	/**
	 * @var	array	consists of Validation_error objects thrown during validation
	 */
	protected $errors = array();

	public function __construct()
	{
		$this->callables = array($this);
	}

	/**
	 * Add field to be validated with title and rules
	 * Note: valid callbacks are everything that is_callable() accepts, including closures
	 *
	 * @param	string	field variable name
	 * @param	string	field title
	 * @param	array	consisting of rules, which are valid callbacks or array(callback, params array)
	 * @return	Validation_Object
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
			return $this;
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
			// first try callables
			$callable_rule = false;
			if ((is_string($r) || (is_array($r) && is_string($r[0])))			// whether callback is a string
				&& ( ! is_array($r) || ( ! isset($r[1]) || is_array($r[1]))))	// whether arguements are valid
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

			// when no callable function was found, try regular callbacks
			if ( ! $callable_rule)
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
					// not found, give a notice but don't break
					if ( ! $callable_rule)
					{
						Error::notice('Invalid rule passed to Validation, not used.');
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Add model
	 *
	 * Add a Fuel Model to callables and expect it to add fields.
	 *
	 * @param	Model
	 * @return	Validation_Object
	 */
	public function add_model($model)
	{
		if ( ! is_callable(array($model, '_fuel_validation')))
		{
			throw new App\Fuel_Exception('Invalid model or no _fuel_validation() method to return fields.');
		}

		/**
		 * The _fuel_validation() method should do a bunch of add_field() calls
		 * on the object it's given (this one).
		 * Note: they don't need to include their model in the callback, can be only string
		 * because $model is added as new first callable.
		 */
		$this->add_callable($model);
		$model->_fuel_validation($this);

		return $this;
	}

	/**
	 * Add Callable
	 *
	 * Adds an object for which you don't need to write a full callback, just
	 * the method as a string will do. This also allows for overwriting functionality
	 * from this object because the new class is prepended.
	 *
	 * @param	object|string	Class or object
	 * @return	Validation_Object
	 */
	public function add_callable($class)
	{
		if ( ! (is_object($class) || class_exists($class)))
		{
			throw new App\Fuel_Exception('Input for add_callable is not a valid object or class.');
		}

		array_unshift($this->callables, $class);

		return $this;
	}

	/**
	 * Run validation
	 *
	 * Performs validation on current field and on given input, will try POST when input
	 * wasn't given.
	 *
	 * @param	array	input that overwrites POST values
	 * @return	bool	whether validation succeeded
	 */
	public function run($input = null)
	{
		$this->output = array();
		$this->errors = array();
		foreach($this->fields as $field => $settings)
		{
			$value = is_null($input) ? Input::post($field, null) : @$input[$field];
			try
			{
				foreach ($settings['rules'] as $rule)
				{
					$callback	= $rule[0];
					$params		= (array) @$rule[1];
					$this->_run_rule($callback, $value, $params, $settings);
				}
				$this->output[$field] = $value;
			}
			catch (Validation_Error $v)
			{
				$this->errors[$field] = $v;
			}
		}

		return empty($this->errors);
	}

	/**
	 * Run rule
	 *
	 * Performs a single rule on a field and its value
	 *
	 * @throws	Validation_Error
	 * @param	callback
	 * @param	mixed	Value by reference, will be edited
	 * @param	array	Extra parameters
	 * @param	array	Validation field description
	 */
	protected function _run_rule($rule, &$value, $params, $field)
	{
		$output = call_user_func_array($rule, array_merge(array($value), $params));

		if ($output === false && $value !== false)
		{
			throw new App\Validation_Error($field, $value, $rule, $params);
		}
		elseif ($output !== true)
		{
			$value = $output;
		}
	}

	/**
	 * Validated
	 *
	 * Returns specific validated value or all validated field=>value pairs
	 *
	 * @param	string		fieldname
	 * @param	mixed		value to return when not validated
	 * @return	array|mixed
	 */
	public function validated($field = false, $default = false)
	{
		if ($field === false)
		{
			return $this->output;
		}

		return array_key_exists($field, $this->output) ? $this->output[$field] : $default;
	}

	/**
	 * Errors
	 *
	 * Return specific error or all errors thrown during validation
	 *
	 * @param	string	fieldname
	 * @param	mixed	value to return when not validated
	 * @return	array|Validation_Error
	 */
	public function errors($field = false, $default = false)
	{
		if ($field === false)
		{
			return $this->errors;
		}

		return array_key_exists($field, $this->errors) ? $this->errors[$field] : $default;
	}

	/**
	 * Show errors
	 *
	 * Returns all errors in a list or with set markup from $options param
	 *
	 * @param	array	uses keys open_list, close_list, open_error, close_error & no_errors
	 * @return	string
	 */
	public function show_errors($options = array())
	{
		$default = array(
			'open_list' => '<ul>',
			'close_list' => '</ul>',
			'open_error' => '<li>',
			'close_error' => '</li>',
			'no_errors' => ''
		);
		$options = array_merge($default, $options);

		if (empty($this->errors))
		{
			return $options['no_errors'];
		}

		$output = $options['open_list'];
		foreach($this->errors as $e)
		{
			$output .= $e->get_message($options['open_error'], $options['close_error']);
		}
		$output .= $options['close_list'];

		return $output;
	}

	// ------------------------------------------------------------------------

	/**
	 * Some validation methods
	 */

	/**
	 * Required
	 *
	 * Value may not be empty
	 *
	 * @param	mixed
	 * @return	bool
	 */
	public function required($val)
	{
		return ($val !== false && $val !== null && $val !== '');
	}

	/**
	 * Match value against comparison input
	 *
	 * @param	mixed
	 * @param	mixed
	 * @param	bool	whether to do type comparison
	 * @return	bool
	 */
	public function match_value($val, $compare, $strict = false)
	{
		// first try direct match
		if ($val === $compare || ( ! $strict && $val == $compare))
		{
			return true;
		}

		// allow multiple input for comparison
		if (is_array($compare))
		{
			foreach($compare as $c)
			{
				if ($val === $c || ( ! $strict && $val == $c))
				{
					return true;
				}
			}
		}

		// all is lost, return failure
		return false;
	}

	/**
	 * Match PRCE pattern
	 *
	 * @param	string
	 * @param	string	a PRCE regex pattern
	 * @return	bool
	 */
	public function match_pattern($val, $pattern)
	{
		return preg_match($pattern, $val) > 0;
	}

	/**
	 * Match specific other submitted field string value
	 * (must be both strings, check is type sensitive)
	 *
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	public function match_field($val, $field)
	{
		return Input::post($field) === $val;
	}

	/**
	 * Minimum string length
	 *
	 * @param	string
	 * @param	int
	 * @return	bool
	 */
	public function min_length($val, $length)
	{
		return (MBSTRING ? mb_strlen($val) : strlen($val)) >= $length;
	}

	/**
	 * Maximum string length
	 *
	 * @param	string
	 * @param	int
	 * @return	bool
	 */
	public function max_length($val, $length)
	{
		return (MBSTRING ? mb_strlen($val) : strlen($val)) <= $length;
	}

	/**
	 * Exact string length
	 *
	 * @param	string
	 * @param	int
	 * @return	bool
	 */
	public function exact_length($val, $length)
	{
		return (MBSTRING ? mb_strlen($val) : strlen($val)) == $length;
	}

	/**
	 * Validate email using PHP's filter_var()
	 *
	 * @param	string
	 * @return	bool
	 */
	public function valid_email($val)
	{
		return filter_var($val, FILTER_VALIDATE_EMAIL);
	}

	/**
	 * Validate email using PHP's filter_var()
	 *
	 * @param	string
	 * @return	bool
	 */
	public function valid_emails($val)
	{
		$emails = explode(',', $val);

		foreach ($emails as $e)
		{
			if ( ! filter_var(trim($e), FILTER_VALIDATE_EMAIL))
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * Validate URL using PHP's filter_var()
	 *
	 * @param	string
	 * @return	bool
	 */
	public function valid_url($val)
	{
		return filter_var($val, FILTER_VALIDATE_URL);
	}

	/**
	 * Validate IP using PHP's filter_var()
	 *
	 * @param	string
	 * @return	bool
	 */
	public function valid_ip($val)
	{
		return filter_var($val, FILTER_VALIDATE_IP);
	}

	/**
	 * Validate input string with many options
	 *
	 * @param	string
	 * @param	string|array	either a named filter or combination of flags
	 * @return	bool
	 */
	public function valid_string($val, $flags = array('alpha', 'utf8'))
	{
		if ( ! is_array($flags))
		{
			if ($flags == 'alpha')
			{
				$flags = array('alpha', 'utf8');
			}
			elseif ($flags == 'alpha_numeric')
			{
				$flags = array('alpha', 'utf8', 'numeric');
			}
			elseif ($flags == 'url_safe')
			{
				$flags = array('alpha', 'numeric', 'dashes');
			}
			elseif ($flags == 'integer')
			{
				$flags = array('numeric');
			}
			elseif ($flags == 'float')
			{
				$flags = array('numeric', 'dots');
			}
			elseif ($flags == 'all')
			{
				$flags = array('alpha', 'utf8', 'numeric', 'spaces', 'newlines', 'tabs', 'punctuation', 'dashes');
			}
			else
			{
				return false;
			}
		}

		$pattern  = '/^([';
		$pattern .= ! in_array('uppercase', $flags) && in_array('alpha', $flags) ? 'a-z' : '';
		$pattern .= ! in_array('lowercase', $flags) && in_array('alpha', $flags) ? 'A-Z' : '';
		$pattern .= in_array('numeric', $flags) ? '0-9' : '';
		$pattern .= in_array('spaces', $flags) ? ' ' : '';
		$pattern .= in_array('newlines', $flags) ? "\n" : '';
		$pattern .= in_array('tabs', $flags) ? "\t" : '';
		$pattern .= in_array('dots', $flags) && ! in_array('punctuation', $flags) ? '\.' : '';
		$pattern .= in_array('punctuation', $flags) ? "\.,\!\?:;" : '';
		$pattern .= in_array('dashes', $flags) ? '_\-' : '';
		$pattern .= '])+$/';
		$pattern .= in_array('utf8', $flags) ? 'u' : '';

		return preg_match($pattern, $val) > 0;
	}

	/**
	 * Checks whether numeric input has a minimum value
	 *
	 * @param	string|float|int
	 * @param	float|int
	 * @return	bool
	 */
	public function numeric_min($val, $min_val)
	{
		return floatval($val) >= floatval($min_val);
	}

	/**
	 * Checks whether numeric input has a maximum value
	 *
	 * @param	string|float|int
	 * @param	float|int
	 * @return	bool
	 */
	public function numeric_max($val, $max_val)
	{
		return floatval($val) <= floatval($max_val);
	}
}

/* End of file object.php */
