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
 * Form Class
 *
 * Create forms via a config file or create forms on the fly.
 *
 * @package		Fuel
 * @category	Core
 * @author		Philip Sturgeon
 */
class Form
{
	/**
	 * @var	array	Used to store form configurations
	 */
	protected static $_forms = array();

	/**
	 * @var array	Used to store the created Form instances
	 */
	protected static $_instances = array();

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
		App\Config::load('form', true);

		static::$_forms = App\Config::get('form.forms');
	}

	// --------------------------------------------------------------------

	public static $default = 'default';


	public static function factory($name = null, array $config = array())
	{
		if ($name === null)
		{
			$name = static::$default;
		}
		elseif (array_key_exists($name, static::$_instances))
		{
			return static::$_instances[$name];
		}

		// This form is pre-configured, lets use the config
		if (isset(static::$_forms[$name]))
		{
			static::$_forms[$name] = array_merge_recursive(static::$_forms[$name], $config);
		}
		// This is new, so assign the config (which hopefully will be an array of fields, or an empty array)
		else
		{
			static::$_forms[$name] = $config;
		}

		// If this is a new instance, fire off the constructor
		static::$_instances[$name] = new Form($name, $config);

		return static::$_instances[$name];
	}

	public static function instance($name = null)
	{
		if ($name === null)
		{
			if (empty(static::$_instances))
			{
				static::factory();
			}

			return reset(static::$_instances);
		}

		if ( ! array_key_exists($name, static::$_instances))
		{
			return false;
		}

		return static::$_instances[$name];
	}

	// --------------------------------------------------------------------

	protected $_form_name;
	protected $_fields = array();
	protected $_attributes = array();
	protected $_validation;

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
			static::add_fields($config['fields']);
		}
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
			throw new App\Exception(sprintf('Field "%s" already exists in form "%s". If you were trying to modify the field, please use $form->modify_field($field_name, $attributes).', $field_name, $this->_form_name));
		}

		$this->_fields[$field_name] = $attributes;
		static::$_forms[$this->_form_name]['fields'][$field_name] = $attributes;

		if ($attributes['type'] == 'file')
		{
			$this->_attributes['enctype'] = 'multipart/form-data';
		}

		if (array_key_exists('validation', $attributes))
		{
			if (empty($this->_validation))
			{
				$this->_validation = App\Validation::factory();
			}

			$this->_validation->add_field($field_name, @$attributes['label'], $attributes['validation']);
			unset($attributes['validation']);
		}
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
	public function modify_field($field_name, $attributes)
	{
		if ( ! $this->field_exists($field_name))
		{
			throw new App\Exception(sprintf('Field "%s" does not exist in form "%s".', $field_name, $this->_form_name));
		}
		$this->_fields[$field_name] = array_merge_recursive($this->_fields[$field_name], $attributes);
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
		if ( ! isset(static::$_forms[$form_name]))
		{
			throw new App\Exception(sprintf('Form "%s" does not exist.', $form_name));
		}

		return static::$_forms[$form_name];
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
	public function create()
	{
		$return = static::open($this->_form_name) . PHP_EOL;
		$return .= static::fields($this->_form_name);
		$return .= static::close() . PHP_EOL;

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
	public function field($name, $properties = array())
	{
		$return = '';

		if ( ! isset($properties['name']))
		{
			$properties['name'] = $name;
		}
		$required = FALSE;
		if ( ! empty($this->_validation) && $field = $this->_validation->get_field($properties['name']))
		{
			foreach ($field->rules as $rule)
			{
				if (reset($rule) === 'required')
				{
					$required = TRUE;
				}
			}
		}

		$return .= static::_open_field($properties['type'], $required);

		switch($properties['type'])
		{
			case 'hidden':
				$return .= "\t\t" . static::input($properties) . PHP_EOL;
				break;
			case 'radio': case 'checkbox':
				$return .= "\t\t\t" . sprintf(App\Config::get('form.label_wrapper_open'), $name) . $properties['label'] . App\Config::get('form.label_wrapper_close') . PHP_EOL;
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
						$return .= "\t\t\t\t" . sprintf(App\Config::get('form.label_wrapper_open'), $element['id']) . $element['label'] . App\Config::get('form.label_wrapper_close') . PHP_EOL;
						$return .= "\t\t\t\t" . static::input($element) . PHP_EOL;
					}
					$return .= "\t\t\t</span>\n";
				}
				else
				{
					$return .= "\t\t\t" . sprintf(App\Config::get('form.label_wrapper_open'), $name) . $properties['label'] . App\Config::get('form.label_wrapper_close') . PHP_EOL;
					$return .= "\t\t\t" . static::input($properties) . PHP_EOL;
				}
				break;
			case 'select':
				$return .= "\t\t\t" . sprintf(App\Config::get('form.label_wrapper_open'), $name) . $properties['label'] . App\Config::get('form.label_wrapper_close') . PHP_EOL;
				$return .= "\t\t\t" . static::select($properties, 3) . PHP_EOL;
				break;
			case 'textarea':
				$return .= "\t\t\t" . sprintf(App\Config::get('form.label_wrapper_open'), $name) . $properties['label'] . App\Config::get('form.label_wrapper_close') . PHP_EOL;
				$return .= "\t\t\t" . static::textarea($properties) . PHP_EOL;
				break;
			default:
				$return .= "\t\t\t" . sprintf(App\Config::get('form.label_wrapper_open'), $name) . $properties['label'] . App\Config::get('form.label_wrapper_close') . PHP_EOL;
				$return .= "\t\t\t" . static::input($properties) . PHP_EOL;
				break;
		}

		$return .= static::_close_field($properties['type'], $required);

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

		$return = "\t\t" . App\Config::get('form.input_wrapper_open') . PHP_EOL;

		if ($required and App\Config::get('form.required_location') == 'before')
		{
			$return .= "\t\t\t" . App\Config::get('form.required_tag') . PHP_EOL;
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

		if ($required and App\Config::get('form.required_location') == 'after')
		{
			$return .= "\t\t\t" . App\Config::get('form.required_tag') . PHP_EOL;
		}

		$return .= "\t\t" . App\Config::get('form.input_wrapper_close') . PHP_EOL;

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
			throw new App\Exception(sprintf('Select element "%s" is either missing the "options" or "options" is not array.', $parameters['name']));
		}
		// Get the options then unset them from the array
		$options = $parameters['options'];
		unset($parameters['options']);

		// Get the selected options then unset it from the array
		$selected = @$parameters['selected'];
		unset($parameters['selected']);

		$input = PHP_EOL;
		foreach ($options as $key => $val)
		{
			if (is_array($val))
			{
				$optgroup = PHP_EOL;
				foreach ($val as $opt_key => $opt_val)
				{
					$opt_attr = array('value' => $opt_key);
					($opt_key == $selected) && $opt_attr[] = 'selected';
					$optgroup .= str_repeat("\t", $indent_amount + 2);
					$optgroup .= html_tag('option', $opt_attr, static::prep_value($opt_val)).PHP_EOL;
				}
				$optgroup .= str_repeat("\t", $indent_amount + 1);
				$input .= str_repeat("\t", $indent_amount + 1).html_tag('optgroup', array('label' => $key), $optgroup).PHP_EOL;
			}
			else
			{
				$opt_attr = array('value' => $key);
				($key == $selected) && $opt_attr[] = 'selected';
				$input .= str_repeat("\t", $indent_amount + 1);
				$input .= html_tag('option', $opt_attr, static::prep_value($val)).PHP_EOL;
			}
		}
		$input .= str_repeat("\t", $indent_amount);

		return html_tag('select', static::attr_to_string($parameters), $input);
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
		if (isset(static::$_instances[$form_name]))
		{
			$form = static::get_form_array($form_name);

			if (isset($form['attributes']))
			{
				$options = array_merge($form['attributes'], $options);
			}
		}

		// There is a form name, but no action is set
		elseif ($form_name and ! isset($options['action']))
		{
			$options['action'] = $form_name;
		}

		// If there is still no action set, Form-post
		if (empty($options['action']))
		{
			$options['action'] = App\Uri::current();
		}

		// If not a full URL, create one
		if ( ! strpos($options['action'], '://'))
		{
			$options['action'] = App\Uri::create($options['action']);
		}

		// If method is empty, use POST
		isset($options['method']) OR $options['method'] = 'post';

		$form = '<form '.static::attr_to_string($options).'>';

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
		$form = static::instance($form_name);

		$return = "\t" . App\Config::get('form.form_wrapper_open') . PHP_EOL;

		foreach ($form->_fields as $name => $properties)
		{
			if($properties['type'] == 'hidden')
			{
				$hidden[$name] = $properties;
				continue;
			}
			$return .= $form->field($name, $properties, $form_name);
		}

		$return .= "\t" . App\Config::get('form.form_wrapper_close') . PHP_EOL;

		foreach ($hidden as $name => $properties)
		{
			if ( ! isset($properties['name']))
			{
				$properties['name'] = $name;
			}
			$return .= "\t" . static::input($properties) . PHP_EOL;
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
		return html_tag('label', (isset($for) ? array('for' => $for) : array()), $value);
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
			throw new App\Exception('You must specify a type for the input.');
		}
		elseif ( ! in_array($options['type'], static::$_valid_inputs))
		{
			throw new App\Exception(sprintf('"%s" is not a valid input type.', $options['type']));
		}

		return html_tag('input', static::attr_to_string($options));
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

		return html_tag('textarea', static::attr_to_string($options), static::prep_value($value));
	}


	// --------------------------------------------------------------------

	/**
	 * Attr to String
	 *
	 * Wraps the global attributes function and does some form specific work
	 *
	 * @access	private
	 * @param	array	$attr
	 * @return	string
	 */
	private static function attr_to_string($attr)
	{
		unset($attr['label']);
		isset($attr['value']) && $attr['value'] = static::prep_value($attr['value']);
		return array_to_attr($attr);
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
	 * Validate
	 *
	 * Runs form validation on the given form
	 *
	 * @access	public
	 * @param	string	$form_name
	 * @return	bool
	 */
	public function run_validation()
	{
		if (empty($this->_validation))
		{
			return true;
		}

		return $this->_validation->run();
	}

	public function validated($field = null, $default = null)
	{
		if (empty($this->_validation))
		{
			return Input::post($field, $default);
		}

		return $this->_validation->validated($field, $default);
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
	public function errors($field_name = null)
	{
		return $this->_validation->errors($field_name);
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
	public function show_errors(Array $config = array())
	{
		return $this->_validation->show_errors($config);
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
	public function set_value($form_name, $field_name, $default = null)
	{
		$post_name = str_replace('[]', '', $field_name);
		$value = isset($_POST[$post_name]) ? $_POST[$post_name] : static::prep_value($default);

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
				$field['value'] = static::prep_value($value);
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
	 * @return	string
	 */
	public function repopulate()
	{
		foreach ($this->_fields as $field_name => $attr)
		{
			static::set_value($this->_form_name, $field_name, (isset($attr['value']) ? $attr['value'] : null));
		}
	}
}

/* End of file form.php */