<?php defined('COREPATH') or die('No direct script access.');
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

// ------------------------------------------------------------------------

/**
 * Form Class
 *
 * Create forms via a config file or create forms on the fly.
 *
 * @package		Fuel
 * @category	Core
 * @author		Philip Sturgeon
 */
class Fuel_Form
{
	public static $initialized = false;

	/**
	 * Used to store the forms
	 */
	protected static $_forms = array();

	/**
	 * Used to store the form_validation info
	 */
	protected static $_validation = array();

	/**
	 * Valid types for input tags (including HTML5)
	 */
	protected static $_valid_inputs = array(
		'button','checkbox','color','date','datetime',
		'datetime-local','email','file','hidden','image',
		'month','number','password','radio','range',
		'reset','search','submit','tel','text','time',
		'url','week'
	);

	// --------------------------------------------------------------------

	/**
	 * When autoloaded this will method will be fired, load once and once only
	 *
	 * @param   string  Ftp filename
	 * @param   array   array of values
	 * @return  void
	 */
	public static function init()
	{
		// Load for the first time
		if (empty(self::$initialized))
		{
			Config::load('form', 'form');

			self::$initialized = true;

			self::$_forms = Config::get('form.forms');
		}
	}

	// --------------------------------------------------------------------

	public static $default = 'default';

	public static $instances = array();

	public static function instance($name = NULL, array $config = array())
	{
		if ($name === NULL)
		{
			$name = Form::$default;
		}

		// This form is already set, lets use the config
		if (isset(self::$_forms[$name]))
		{
			self::$_forms[$name] = array_merge_recursive(self::$_forms[$name], $config);
		}

		// This is new, so assign the config (which hopefully will be an array of fields, or an empty array)
		else
		{
			self::$_forms[$name] = $config;
		}

		// If this is a new instance, fire off the constructor
		if ( ! isset(Form::$instances[$name]))
		{
			Form::$instances[$name] = new Form($name, $config);
		}

		return Form::$instances[$name];
	}

	// --------------------------------------------------------------------

	protected $_form_name = 'default';
	protected $_fields = array();
	protected $_attributes = array();

	/**
	 * Construct
	 *
	 * Create
	 *
	 * @access	public
	 * @param	array	$custom_config
	 */
	public function __construct($name = NULL, array $config = array())
	{
		$this->_form_name = $name;

		if(isset($config['attributes']))
		{
			$this->_attributes = array_merge_recursive($this->_attributes, $config['attributes']);
		}

		if (isset($config['fields']))
		{
			Form::add_fields($config['fields']);
		}

		Form::parse_validation();
	}
	
	// --------------------------------------------------------------------

	/**
	 * Add Field
	 *
	 * Adds a field to a given form
	 *
	 * @access	public
	 * @param	string	$form_name
	 * @param	string	$field_name
	 * @param	array	$attributes
	 * @return	void
	 */
	public function add_field($field_name, $attributes)
	{
		if ($this->field_exists($field_name))
		{
			throw new Fuel_Exception(sprintf('Field "%s" already exists in form "%s". If you were trying to modify the field, please use $form->modify_field($field_name, $attributes).', $field_name, $this->_form_name));
		}

		$this->_fields[$field_name] = $attributes;

		if ($attributes['type'] == 'file')
		{
			$this->_attributes['enctype'] = 'multipart/form-data';
		}

		$this->parse_validation();
	}

	// --------------------------------------------------------------------

	/**
	 * Add Fields
	 *
	 * Allows you to add multiple fields at once.
	 *
	 * @access	public
	 * @param	string	$form_name
	 * @param	array	$fields
	 * @return	void
	 */
	public function add_fields($fields)
	{
		foreach ($fields as $field_name => $attributes)
		{
			$this->add_field($field_name, $attributes);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Modify Field
	 *
	 * Allows you to modify a field.
	 *
	 * @access	public
	 * @param	string	$form_name
	 * @param	string	$field_name
	 * @param	array	$attributes
	 * @return	void
	 */
	public static function modify_field($field_name, $attributes)
	{
		if ( ! $this->field_exists($field_name))
		{
			throw new Fuel_Exception(sprintf('Field "%s" does not exist in form "%s".', $field_name, $this->_form_name));
		}
		$this->_fields[$field_name] = array_merge_recursive($this->_fields[$field_name], $attributes);

		$this->parse_validation();
	}

	// --------------------------------------------------------------------

	/**
	 * Modify Fields
	 *
	 * Allows you to modify multiple fields at once.
	 *
	 * @access	public
	 * @param	string	$form_name
	 * @param	array	$fields
	 * @return	void
	 */
	public function modify_fields($fields)
	{
		foreach ($fields as $field_name => $attributes)
		{
			$this->modfy_field($field_name, $attributes);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Field Exists
	 *
	 * Checks if a field exists.
	 *
	 * @param	string	$form_name
	 * @param	string	$field_name
	 * @return	bool
	 */
	public function field_exists($field_name)
	{
		return isset($this->_fields[$field_name]);
	}

	// --------------------------------------------------------------------

	/**
	 * Get Form Array
	 *
	 * Returns the form with all fields and options as an array
	 *
	 * @access	private
	 * @param	string	$form_name
	 * @return	array
	 */
	private static function get_form_array($form_name)
	{
		if ( ! isset(Form::$_forms[$form_name]))
		{
			throw new Fuel_Exception(sprintf('Form "%s" does not exist.', $form_name));
		}

		return Form::$_forms[$form_name];
	}

	// --------------------------------------------------------------------

	/**
	 * Form
	 *
	 * Builds a form and returns well-formatted, valid XHTML for output.
	 *
	 * @access	public
	 * @param	string	$form_name
	 * @return	string
	 */
	public static function form($form_name)
	{
		$form = Form::get_form_array($form_name);

		$return = Form::open($form_name) . PHP_EOL;
		$return .= Form::fields($form_name);
		$return .= Form::close() . PHP_EOL;

		return $return;
	}

	// --------------------------------------------------------------------

	/**
	 * Field
	 *
	 * Builds a field and returns well-formatted, valid XHTML for output.
	 *
	 * @access	public
	 * @param	string	$name
	 * @param	string	$properties
	 * @param	string	$form_name
	 * @return	string
	 */
	public static function field($name, $properties = array(), $form_name = null)
	{
		$return = '';

		if ( ! isset($properties['name']))
		{
			$properties['name'] = $name;
		}
		$required = FALSE;
		if (isset(Form::$_validation[$form_name]))
		{
			foreach (Form::$_validation[$form_name] as $rule)
			{
				if ($rule['field'] == $properties['name'] and $rule['rules'] and strpos('required', $rule['rules']) !== FALSE)
				{
					$required = TRUE;
				}
			}
		}

		$return .= Form::_open_field($properties['type'], $required);

		switch($properties['type'])
		{
			case 'hidden':
				$return .= "\t\t" . Form::input($properties) . PHP_EOL;
				break;
			case 'radio': case 'checkbox':
				$return .= "\t\t\t" . sprintf(Config::get('form.label_wrapper_open'), $name) . $properties['label'] . Config::get('form.label_wrapper_close') . PHP_EOL;
				if (isset($properties['items']))
				{
					$return .= "\t\t\t<span>\n";

					if ($properties['type'] == 'checkbox' and count($properties['items']) > 1)
					{
						// More than one item exists, this should probably be an array
						if (substr($properties['name'], -2) != '[]')
						{
							$properties['name'] .= '[]';
						}
					}

					foreach ($properties['items'] as $count => $element)
					{
						if ( ! isset($element['id']))
						{
							$element['id'] = str_replace('[]', '', $name) . '_' . $count;
						}

						$element['type'] = $properties['type'];
						$element['name'] = $properties['name'];
						$return .= "\t\t\t\t" . sprintf(Config::get('form.label_wrapper_open'), $element['id']) . $element['label'] . Config::get('form.label_wrapper_close') . PHP_EOL;
						$return .= "\t\t\t\t" . Form::input($element) . PHP_EOL;
					}
					$return .= "\t\t\t</span>\n";
				}
				else
				{
					$return .= "\t\t\t" . sprintf(Config::get('form.label_wrapper_open'), $name) . $properties['label'] . Config::get('form.label_wrapper_close') . PHP_EOL;
					$return .= "\t\t\t" . Form::input($properties) . PHP_EOL;
				}
				break;
			case 'select':
				$return .= "\t\t\t" . sprintf(Config::get('form.label_wrapper_open'), $name) . $properties['label'] . Config::get('form.label_wrapper_close') . PHP_EOL;
				$return .= "\t\t\t" . Form::select($properties, 3) . PHP_EOL;
				break;
			case 'textarea':
				$return .= "\t\t\t" . sprintf(Config::get('form.label_wrapper_open'), $name) . $properties['label'] . Config::get('form.label_wrapper_close') . PHP_EOL;
				$return .= "\t\t\t" . Form::textarea($properties) . PHP_EOL;
				break;
			default:
				$return .= "\t\t\t" . sprintf(Config::get('form.label_wrapper_open'), $name) . $properties['label'] . Config::get('form.label_wrapper_close') . PHP_EOL;
				$return .= "\t\t\t" . Form::input($properties) . PHP_EOL;
				break;
		}

		$return .= Form::_close_field($properties['type'], $required);

		return $return;
	}

	// --------------------------------------------------------------------

	/**
	 * Open Field
	 *
	 * Generates the fields opening tags.
	 *
	 * @access	private
	 * @param	string	$type
	 * @param	bool	$required
	 * @return	string
	 */
	private static function _open_field($type, $required = FALSE)
	{
		if($type == 'hidden')
		{
			return '';
		}

		$return = "\t\t" . Config::get('form.input_wrapper_open') . PHP_EOL;

		if ($required and Config::get('form.required_location') == 'before')
		{
			$return .= "\t\t\t" . Config::get('form.required_tag') . PHP_EOL;
		}

		return $return;
	}

	// --------------------------------------------------------------------

	/**
	 * Close Field
	 *
	 * Generates the fields closing tags.
	 *
	 * @access	private
	 * @param	string	$type
	 * @param	bool	$required
	 * @return	string
	 */
	private static function _close_field($type, $required = FALSE)
	{
		if($type == 'hidden')
		{
			return '';
		}

		$return = "";

		if ($required and Config::get('form.required_location') == 'after')
		{
			$return .= "\t\t\t" . Config::get('form.required_tag') . PHP_EOL;
		}

		$return .= "\t\t" . Config::get('form.input_wrapper_close') . PHP_EOL;

		return $return;
	}

	// --------------------------------------------------------------------

	/**
	 * Select
	 *
	 * Generates a <select> element based on the given parameters
	 *
	 * @access	public
	 * @param	array	$parameters
	 * @param	int		$indent_amount
	 * @return	string
	 */
	public static function select($parameters, $indent_amount = 0)
	{
		if ( ! isset($parameters['options']) OR !is_array($parameters['options']))
		{
			throw new Fuel_Exception(sprintf('Select element "%s" is either missing the "options" or "options" is not array.', $parameters['name']));
		}
		// Get the options then unset them from the array
		$options = $parameters['options'];
		unset($parameters['options']);

		// Get the selected options then unset it from the array
		$selected = $parameters['selected'];
		unset($parameters['selected']);

		$input = '<select ' . Form::attr_to_string($parameters) . '>' . PHP_EOL;
		foreach ($options as $key => $val)
		{
			if (is_array($val))
			{
				$input .= str_repeat("\t", $indent_amount + 1) . '<optgroup label="' . $key . '">' . PHP_EOL;
				foreach ($val as $opt_key => $opt_val)
				{
					$extra = ($opt_key == $selected) ? ' selected="selected"' : '';
					$input .= str_repeat("\t", $indent_amount + 2);
					$input .= '<option value="' . $opt_key . '"' . $extra . '>' . Form::prep_value($opt_val) . "</option>\n";
				}
				$input .= str_repeat("\t", $indent_amount + 1) . '</optgroup>' . PHP_EOL;
			}
			else
			{
				$extra = ($key == $selected) ? ' selected="selected"' : '';
				$input .= str_repeat("\t", $indent_amount + 1);
				$input .= '<option value="' . $key . '"' . $extra . '>' . Form::prep_value($val) . "</option>\n";
			}
		}
		$input .= str_repeat("\t", $indent_amount) . "</select>";

		return $input;
	}

	// --------------------------------------------------------------------

	/**
	 * Open
	 *
	 * Generates the opening <form> tag
	 *
	 * @access	public
	 * @param	string	$action
	 * @param	array	$options
	 * @return	string
	 */
	public static function open($form_name = null, $options = array())
	{
		// The form name does not exist, must be an action as its not set in options either
		if (isset(Form::$_forms[$form_name]))
		{
			$form = Form::get_form_array($form_name);

			if (isset($form['attributes']))
			{
				$options = array_merge($form['attributes'], $options);
			}
		}

		// There is a form name, but no action is set
		elseif ( $form_name and ! isset($options['action']))
		{
			$options['action'] = $form_name;
		}

		// If there is still no action set, Form-post
		if (empty($options['action']))
		{
			$options['action'] = URL::current();
		}

		// If not a full URL, create one with CI
		if ( ! strpos($options['action'], '://'))
		{
			$options['action'] = URL::href($options['action']);
		}

		// If method is empty, use POST
		isset($options['method']) OR $options['method'] = 'post';

		$form = '<form ' . Form::attr_to_string($options) . '>';

		return $form;
	}

	// --------------------------------------------------------------------

	/**
	 * Fields
	 *
	 * Generates the list of fields without the form open and form close tags
	 *
	 * @access	public
	 * @param	string	$action
	 * @param	array	$options
	 * @return	string
	 */
	public static function fields($form_name)
	{
		$hidden = array();
		$form = Form::get_form_array($form_name);

		$return = "\t" . Config::get('form.form_wrapper_open') . PHP_EOL;

		foreach ($form['fields'] as $name => $properties)
		{
			if($properties['type'] == 'hidden')
			{
				$hidden[$name] = $properties;
				continue;
			}
			$return .= Form::field($name, $properties, $form_name);
		}

		$return .= "\t" . Config::get('form.form_wrapper_close') . PHP_EOL;

		foreach ($hidden as $name => $properties)
		{
			if ( ! isset($properties['name']))
			{
				$properties['name'] = $name;
			}
			$return .= "\t" . Form::input($properties) . PHP_EOL;
		}

		return $return;
	}

	// --------------------------------------------------------------------

	/**
	 * Close
	 *
	 * Generates the closing </form> tag
	 *
	 * @access	public
	 * @return	string
	 */
	public static function close()
	{
		return '</form>';
	}

	// --------------------------------------------------------------------

	/**
	 * Label
	 *
	 * Generates a label based on given parameters
	 *
	 * @access	public
	 * @param	string	$value
	 * @param	string	$for
	 * @return	string
	 */
	public static function label($value, $for = null)
	{
		return $for === null
			? '<label>' . $value . '</label>'
			: '<label for="' . $for . '">' . $value . '</label>';
	}

	// --------------------------------------------------------------------

	/**
	 * Input
	 *
	 * Generates an <input> tag
	 *
	 * @access	public
	 * @param	array	$options
	 * @return	string
	 */
	public static function input($options)
	{
		if ( ! isset($options['type']))
		{
			throw new Fuel_Exception('You must specify a type for the input.');
		}
		elseif ( ! in_array($options['type'], Form::$_valid_inputs))
		{
			throw new Fuel_Exception(sprintf('"%s" is not a valid input type.', $options['type']));
		}
		$input = '<input ' . Form::attr_to_string($options) . ' />';

		return $input;
	}

	// --------------------------------------------------------------------

	/**
	 * Textarea
	 *
	 * Generates a <textarea> tag
	 *
	 * @access	public
	 * @param	array	$options
	 * @return	string
	 */
	public static function textarea($options)
	{
		$value = '';
		if (isset($options['value']))
		{
			$value = $options['value'];
			unset($options['value']);
		}
		$input = "<textarea " . Form::attr_to_string($options) . '>';
		$input .= Form::prep_value($value);
		$input .= '</textarea>';

		return $input;
	}


	// --------------------------------------------------------------------

	/**
	 * Attr to String
	 *
	 * Takes an array of attributes and turns it into a string for an input
	 *
	 * @access	private
	 * @param	array	$attr
	 * @return	string
	 */
	private function attr_to_string($attr)
	{
		$attr_str = '';

		if ( ! is_array($attr))
		{
			$attr = (array) $attr;
		}

		foreach ($attr as $property => $value)
		{
			if ($property == 'label')
			{
				continue;
			}
			if ($property == 'value')
			{
				$value = Form::prep_value($value);
			}
			$attr_str .= $property . '="' . $value . '" ';
		}

		// We strip off the last space for return
		return substr($attr_str, 0, -1);
	}

	// --------------------------------------------------------------------

	/**
	 * Prep Value
	 *
	 * Prepares the value for display in the form
	 *
	 * @access	public
	 * @param	string	$value
	 * @return	string
	 */
	public static function prep_value($value)
	{
		$value = htmlspecialchars($value);
		$value = str_replace(array("'", '"'), array("&#39;", "&quot;"), $value);

		return $value;
	}

	// --------------------------------------------------------------------

	/**
	 * Parse Validation
	 *
	 * Adds the validation rules in each field to the $_validation array
	 * and removes it from the field attributes
	 *
	 * @access	private
	 * @return	void
	 */
	private static function parse_validation()
	{
		foreach (Form::$_forms as $form_name => $form)
		{
			if ( ! isset($form['fields']))
			{
				continue;
			}

			$i = 0;
			foreach ($form['fields'] as $name => $attr)
			{
				if (isset($attr['validation']))
				{
					Form::$_validation[$form_name][$i]['field'] = $name;
					Form::$_validation[$form_name][$i]['label'] = $attr['label'];
					Form::$_validation[$form_name][$i]['rules'] = $attr['validation'];

					unset($this->_fields[$name]['validation']);
				}

				++$i;
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Validate
	 *
	 * Runs form validation on the given form
	 *
	 * @access	public
	 * @param	string	$form_name
	 * @return	bool
	 */
	public static function validate($form_name)
	{
		// #TODO: Add validation support
		return true;
		
		if ( ! isset(Form::$_validation[$form_name]))
		{
			return TRUE;
		}

		Validation::set_rules(Form::$_validation[$form_name]);

		return Validation::run();
	}

	// --------------------------------------------------------------------

	/**
	 * Error
	 *
	 * Returns a single form validation error
	 *
	 * @access	public
	 * @param	string	$field_name
	 * @param	string	$prefix
	 * @param	string	$suffix
	 * @return	string
	 */
	public static function error($field_name, $prefix = '', $suffix = '')
	{
		return Validation::error($field_name, $prefix, $suffix);
	}

	// --------------------------------------------------------------------

	/**
	 * All Errors
	 *
	 * Returns all of the form validation errors
	 *
	 * @access	public
	 * @param	string	$prefix
	 * @param	string	$suffix
	 * @return	string
	 */
	public static function all_errors($prefix = '', $suffix = '')
	{
		return Validation::error_string($prefix, $suffix);
	}

	// --------------------------------------------------------------------

	/**
	 * Set Value
	 *
	 * Set's a fields value
	 *
	 * @access	public
	 * @param	string	$form_name
	 * @param	string	$field_name
	 * @param	mixed	$value
	 * @return	void
	 */
	public static function set_value($form_name, $field_name, $default = null)
	{
		$post_name = str_replace('[]', '', $field_name);
		$value = isset($_POST[$post_name]) ? $_POST[$post_name] : Form::prep_value($default);

		$field =& $this->_fields[$field_name];

		switch($field['type'])
		{
			case 'radio': case 'checkbox':
				if (isset($field['items']))
				{
					foreach ($field['items'] as &$element)
					{
						if (is_array($value))
						{
							if (in_array($element['value'], $value))
							{
								$element['checked'] = 'checked';
							}
							else
							{
								if (isset($element['checked']))
								{
									unset($element['checked']);
								}
							}
						}
						else
						{
							if ($element['value'] === $value)
							{
								$element['checked'] = 'checked';
							}
							else
							{
								if (isset($element['checked']))
								{
									unset($element['checked']);
								}
							}
						}
					}
				}
				else
				{
					$field['value'] = $value;
				}
				break;
			case 'select':
				$field['selected'] = $value;
				break;
			default:
				$field['value'] = Form::prep_value($value);
				break;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Repopulate
	 *
	 * Repopulates the entire form with the submitted data.
	 *
	 * @access	public
	 * @param	string	$form_name
	 * @return	string
	 */
	public static function repopulate($form_name)
	{
		foreach ($this->_fields as $field_name => $attr)
		{
			Form::set_value($form_name, $field_name, (isset($attr['value']) ? $attr['value'] : null));
		}
	}
}

/* End of file form.php */