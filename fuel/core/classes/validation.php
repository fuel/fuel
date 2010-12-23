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
 * Validation
 *
 * Static object to allow static usage of validation through singleton.
 *
 * @package		Fuel
 * @subpackage	Core
 * @category	Core
 * @author		Jelmer Schreuder
 */
class Validation {

	public static function factory($fieldset)
	{
		if ( ! $fieldset instanceof Fieldset)
		{
			$fieldset = (string) $fieldset;
			if ( ! ($fieldset = App\Fieldset::instance($fieldset)))
			{
				$fieldset = App\Fieldset::factory($fieldset);
			}
		}
		return new Validation($fieldset);
	}

	public static function instance($name = null)
	{
		$fieldset = App\Fieldset::instance($name);
		return $fieldset === false ? false : $fieldset->validation();
	}

	/**
	 * @var	Fieldset
	 */
	protected $fieldset;

	/**
	 * @var	array	available after validation started running: contains given input values
	 */
	protected $input = array();

	/**
	 * @var	array	contains values of fields that validated succesfully
	 */
	protected $validated = array();

	/**
	 * @var	array	contains Validation_Error instances of encountered errors
	 */
	protected $errors = array();

	/**
	 * @var	array	contains a list of classnames and objects that may contain validation methods
	 */
	protected $callables = array();

	protected function __construct(Fieldset $fieldset)
	{
		$this->fieldset = $fieldset;
		$this->callables = array($this);
	}

	/**
	 * Returns the related fieldset
	 *
	 * @return	Fieldset
	 */
	public function fieldset()
	{
		return $this->fieldset;
	}

	/**
	 * Add Callable
	 *
	 * Adds an object for which you don't need to write a full callback, just
	 * the method as a string will do. This also allows for overwriting functionality
	 * from this object because the new class is prepended.
	 *
	 * @param	string|Object	Classname or object
	 * @return	Validation		this, to allow chaining
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
	 * Fetch the objects for which you don't need to add a full callback but
	 * just the method name
	 *
	 * @return	array
	 */
	public function callables()
	{
		return $this->callables;
	}

	/**
	 * Run validation
	 *
	 * Performs validation with current fieldset and on given input, will try POST
	 * when input wasn't given.
	 *
	 * @param	array	input that overwrites POST values
	 * @param	bool	will skip validation of values it can't find or are null
	 * @return	bool	whether validation succeeded
	 */
	public function run($input = null, $allow_partial = false)
	{
		if (empty($input) && empty($_POST))
		{
			return false;
		}

		$this->validated = array();
		$this->errors = array();
		$this->input = $input ?: array();
		foreach($this->fields as $field)
		{
			$value = $this->input($field->key);
			if ($allow_partial && $value === null)
			{
				continue;
			}
			try
			{
				foreach ($field->rules as $rule)
				{
					$callback	= $rule[0];
					$params		= $rule[1];
					$this->_run_rule($callback, $value, $params, $field);
				}
				$this->validated[$field->key] = $value;
			}
			catch (Validation_Error $v)
			{
				$this->errors[$field->key] = $v;
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
	 * Fetches the input value from either post or given input
	 *
	 * @param	string
	 * @param	mixed
	 * @return	mixed
	 */
	public function input($key = null, $default = null)
	{
		if ($key === null)
		{
			return $this->input;
		}

		if ( ! array_key_exists($key, $this->input))
		{
			$this->input[$key] = Input::post($key, $default);
		}

		return $this->input[$key];
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
	public function validated($field = null, $default = false)
	{
		if ($field === null)
		{
			return $this->validated;
		}

		return array_key_exists($field, $this->validated) ? $this->validated[$field] : $default;
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
	public function errors($field = null, $default = false)
	{
		if ($field === null)
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
			$output .= $options['open_error'].$e->get_message().$options['close_error'];
		}
		$output .= $options['close_list'];

		return $output;
	}

	/**
	 * Alias for $this->fieldset->add()
	 */
	public function add($name, $label = '', array $attributes = array(), array $rules = array())
	{
		return $this->fieldset->add($name, $label, $attributes, $rules);
	}

	/**
	 * Alias for $this->fieldset->add_model()
	 *
	 * @return	Validation	this, to allow chaining
	 */
	public function add_model($class, $instance = null, $method = 'set_form_fields')
	{
		$this->fieldset->add_model($class);

		return $this;
	}

	/**
	 * Alias for $this->fieldset->field()
	 */
	public function field($name = null)
	{
		return $this->fieldset->field($name);
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
	public function _validation_required($val)
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
	public function _validation_match_value($val, $compare, $strict = false)
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
	public function _validation_match_pattern($val, $pattern)
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
	public function _validation_match_field($val, $field)
	{
		return $this->input($field) === $val;
	}

	/**
	 * Minimum string length
	 *
	 * @param	string
	 * @param	int
	 * @return	bool
	 */
	public function _validation_min_length($val, $length)
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
	public function _validation_max_length($val, $length)
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
	public function _validation_exact_length($val, $length)
	{
		return (MBSTRING ? mb_strlen($val) : strlen($val)) == $length;
	}

	/**
	 * Validate email using PHP's filter_var()
	 *
	 * @param	string
	 * @return	bool
	 */
	public function _validation_valid_email($val)
	{
		return empty($val) || filter_var($val, FILTER_VALIDATE_EMAIL);
	}

	/**
	 * Validate email using PHP's filter_var()
	 *
	 * @param	string
	 * @return	bool
	 */
	public function _validation_valid_emails($val)
	{
		if (empty($val))
		{
			return true;
		}

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
	public function _validation_valid_url($val)
	{
		return empty($val) || filter_var($val, FILTER_VALIDATE_URL);
	}

	/**
	 * Validate IP using PHP's filter_var()
	 *
	 * @param	string
	 * @return	bool
	 */
	public function _validation_valid_ip($val)
	{
		return empty($val) || filter_var($val, FILTER_VALIDATE_IP);
	}

	/**
	 * Validate input string with many options
	 *
	 * @param	string
	 * @param	string|array	either a named filter or combination of flags
	 * @return	bool
	 */
	public function _validation_valid_string($val, $flags = array('alpha', 'utf8'))
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
	public function _validation_numeric_min($val, $min_val)
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
	public function _validation_numeric_max($val, $max_val)
	{
		return floatval($val) <= floatval($max_val);
	}
}

/* End of file validation.php */
