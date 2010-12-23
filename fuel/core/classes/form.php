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
 * Helper for creating forms with support for creating dynamic form objects.
 *
 * @package		Fuel
 * @category	Core
 * @author		Philip Sturgeon, Jelmer Schreuder
 */
class Form {

	/* ----------------------------------------------------------------------------
	 * Factory & instance methods
	 * ---------------------------------------------------------------------------- */

	public static function factory($fieldset, $config)
	{
		if ( ! $fieldset instanceof Fieldset)
		{
			$fieldset = (string) $fieldset;
			if ( ! ($fieldset = App\Fieldset::instance($fieldset)))
			{
				$fieldset = App\Fieldset::factory($fieldset, $config);
			}
		}
		return new Form($fieldset);
	}

	public static function instance($name = null)
	{
		$fieldset = App\Fieldset::instance($name);
		return $fieldset === false ? false : $fieldset->validation();
	}

	/* ----------------------------------------------------------------------------
	 * Class static properties & methods
	 * ---------------------------------------------------------------------------- */

	/**
	 * @var	array	default form config array
	 */
	protected static $class_config = array(
		'prep_value'		=> true,
		'auto_id'			=> true,
		'auto_id_prefix'	=> 'form_',
		'form_method'		=> 'post'
	);

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

		static::$class_config = App\Config::get('form');
	}

	/**
	 * Sets a form class config value
	 *
	 * @param	string
	 * @param	mixed	new value or null to unset
	 */
	public static function set_class_config($config, $value = null)
	{
		$config = is_array($config) ? $config : array($config => $value);
		foreach ($config as $key => $value)
		{
			if ($value === null)
			{
				unset(static::$class_config[$key]);
			}
			else
			{
				static::$class_config[$key] = $value;
			}
		}
	}

	/**
	 * Get a single or multiple config values by key
	 *
	 * @param	string|array	a single key or multiple in an array, empty to fetch all
	 * @param	mixed			default output when config wasn't set
	 * @return	mixed|array		a single config value or multiple in an array when $key input was an array
	 */
	public static function get_class_config($key = null, $default = null)
	{
		if ($key === null)
		{
			return static::$class_config;
		}

		if (is_array($key))
		{
			$output = array();
			foreach ($key as $k)
			{
				$output[$k] = array_key_exists($k, static::$class_config) ? static::$class_config[$k] : $default;
			}
			return $output;
		}

		return array_key_exists($key, static::$class_config) ? static::$class_config[$key] : $default;
	}

	/**
	 * Create a form open tag
	 *
	 * @param	string|array	action string or array with more tag attribute settings
	 * @return	string
	 */
	public static function open($attributes, Array $hidden = array())
	{
		$attributes = ! is_array($attributes) ? array('action' => (string) $attributes) : $attributes;

		// If there is still no action set, Form-post
		empty($attributes['action']) && $attributes['action'] = App\Uri::current();

		// If not a full URL, create one
		! strpos($attributes['action'], '://') && $attributes['action'] = App\Uri::create($attributes['action']);

		// If method is empty, use POST
		! empty($attributes['method']) || $attributes['method'] = static::get_class_config('form_method', 'post');

		$form = '<form';
		foreach ($attributes as $prop => $value)
		{
			$form .= ' '.$prop.'="'.$value.'"';
		}
		$form .= '>';

		// Add hidden fields when given
		foreach ($hidden as $field => $value)
		{
			$form .= PHP_EOL.static::hidden($field, $value);
		}

		return $form;
	}

	/**
	 * Create a form close tag
	 *
	 * @return string
	 */
	public static function close()
	{
		return '</form>';
	}

	/**
	 * Create a form input
	 *
	 * @param	string|array	either fieldname or full attributes array (when array other params are ignored)
	 * @param	string
	 * @param	array
	 * @return	string
	 */
	public static function input($field, $value = null, Array $attributes = array())
	{
		if (is_array($field))
		{
			$attributes = $field;
		}
		else
		{
			$attributes['name'] = (string) $field;
			$attributes['value'] = (string) $value;
		}

		$attributes['type'] = @$attributes['type'] ?: 'text';

		if ( ! in_array($attributes['type'], static::$_valid_inputs))
		{
			throw new App\Exception(sprintf('"%s" is not a valid input type.', $attributes['type']));
		}

		if (static::get_class_config('prep_value', true) || empty($attributes['dont_prep']))
		{
			$attributes['value'] = static::prep_value($attributes['value']);
			unset($attributes['dont_prep']);
		}

		if (empty($attributes['id']) && static::get_class_config('auto_id', false) == true)
		{
			$attributes['id'] = static::get_class_config('auto_id_prefix', '').$attributes['name'];
		}

		return html_tag('input', static::attr_to_string($attributes));
	}

	/**
	 * Create a hidden field
	 *
	 * @param	string|array	either fieldname or full attributes array (when array other params are ignored)
	 * @param	string
	 * @param	array
	 * @return
	 */
	public static function hidden($field, $value = null, Array $attributes = array())
	{
		if (is_array($field))
		{
			$attributes = $field;
		}
		else
		{
			$attributes['name'] = (string) $field;
			$attributes['value'] = (string) $value;
		}
		$attributes['type'] = 'hidden';

		return static::input($attributes);
	}

	/**
	 * Create a radio button
	 *
	 * @param	string|array	either fieldname or full attributes array (when array other params are ignored)
	 * @param	string
	 * @param	array
	 * @return
	 */
	public static function radio($field, $value = null, Array $attributes = array())
	{
		if (is_array($field))
		{
			$attributes = $field;
		}
		else
		{
			$attributes['name'] = (string) $field;
			$attributes['value'] = (string) $value;
		}
		$attributes['type'] = 'radio';

		return static::input($attributes);
	}

	/**
	 * Create a checkbox
	 *
	 * @param	string|array	either fieldname or full attributes array (when array other params are ignored)
	 * @param	string
	 * @param	array
	 * @return
	 */
	public static function checkbox($field, $value = null, Array $attributes = array())
	{
		if (is_array($field))
		{
			$attributes = $field;
		}
		else
		{
			$attributes['name'] = (string) $field;
			$attributes['value'] = (string) $value;
		}
		$attributes['type'] = 'checkbox';

		return static::input($attributes);
	}

	/**
	 * Create a textarea field
	 *
	 * @param	string|array	either fieldname or full attributes array (when array other params are ignored)
	 * @param	string
	 * @param	array
	 * @return	string
	 */
	public static function textarea($field, $value = null, Array $attributes = array())
	{
		if (is_array($field))
		{
			$attributes = $field;
		}
		else
		{
			$attributes['name'] = (string) $field;
			$attributes['value'] = (string) $value;
		}

		$value = @$attributes['value'];
		unset($attributes['value']);

		if (static::get_class_config('prep_value', true) || empty($attributes['dont_prep']))
		{
			$value = static::prep_value($value);
			unset($attributes['dont_prep']);
		}

		if (empty($attributes['id']) && static::get_class_config('auto_id', false) == true)
		{
			$attributes['id'] = static::get_class_config('auto_id_prefix', '').$attributes['name'];
		}

		return html_tag('textarea', static::attr_to_string($attributes), static::prep_value($value));
	}

	/**
	 * Select
	 *
	 * Generates a <select> element based on the given parameters
	 *
	 * @param	array
	 * @return	string
	 */
	public static function select($field, $value = null, Array $options = array(), Array $attributes = array())
	{
		if (is_array($field))
		{
			$attributes = $field;
		}
		else
		{
			$attributes['name'] = (string) $field;
			$attributes['selected'] = (string) $value;
			$attributes['options'] = $options;
		}

		$value = @$attributes['value'];
		unset($attributes['value']);

		if ( ! isset($attributes['options']) || ! is_array($attributes['options']))
		{
			throw new App\Exception(sprintf('Select element "%s" is either missing the "options" or "options" is not array.', $attributes['name']));
		}
		// Get the options then unset them from the array
		$options = $attributes['options'];
		unset($attributes['options']);

		// Get the selected options then unset it from the array
		$selected = @$attributes['selected'];
		unset($attributes['selected']);

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
					$optgroup .= str_repeat("\t", 2);
					$optgroup .= html_tag('option', $opt_attr, static::prep_value($opt_val)).PHP_EOL;
				}
				$optgroup .= str_repeat("\t", 1);
				$input .= str_repeat("\t", 1).html_tag('optgroup', array('label' => $key), $optgroup).PHP_EOL;
			}
			else
			{
				$opt_attr = array('value' => $key);
				($key == $selected) && $opt_attr[] = 'selected';
				$input .= str_repeat("\t", 1);
				$input .= html_tag('option', $opt_attr, static::prep_value($val)).PHP_EOL;
			}
		}
		$input .= str_repeat("\t", 0);

		return html_tag('select', static::attr_to_string($attributes), $input);
	}

	/**
	 * Create a label field
	 *
	 * @param	string|array	either fieldname or full attributes array (when array other params are ignored)
	 * @param	string
	 * @param	array
	 * @return	string
	 */
	public static function label($label, $id = null, Array $attributes = array())
	{
		if (is_array($label))
		{
			$label = $attributes['label'];
			$id = $attributes['id'];
		}

		$attributes['for'] = $id;
		unset($attributes['label']);
		unset($attributes['id']);

		return html_tag('label', $attributes, $label);
	}

	/**
	 * Prep Value
	 *
	 * Prepares the value for display in the form
	 *
	 * @param	string
	 * @return	string
	 */
	public static function prep_value($value)
	{
		$value = htmlspecialchars($value);
		$value = str_replace(array("'", '"'), array("&#39;", "&quot;"), $value);

		return $value;
	}

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
		return array_to_attr($attr);
	}

	/* ----------------------------------------------------------------------------
	 * Class dynamic properties & methods
	 * ---------------------------------------------------------------------------- */

	/**
	 * @var	Fieldset
	 */
	protected $fieldset;

	protected function __construct(Fieldset $fieldset)
	{
		$this->fieldset = $fieldset;
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
	 * Build the form
	 *
	 * @return string
	 */
	public function build($action = null)
	{
		$attributes = $this->get_config('form_attributes');
		$action && $attributes['action'] = $action;

		$output = static::open($attributes).PHP_EOL;
		$fields = $this->field();
		foreach ($fields as $f)
		{
			$output .= $this->build_field($f).PHP_EOL;
		}
		$output .= static::close();

		return $output;
	}

	/**
	 * Build & template individual field
	 *
	 * @param	string|Fieldset_Field	field instance or name of a field in this form's fieldset
	 * @return	string
	 */
	public function build_field($field)
	{
		! $field instanceof Fieldset_Field && $field = $this->field($field);

		$required = $field->get_attribute('required', null);
		$field->set_attribute('required', null);
		if ($required === null)
		{
			$required = false;
			foreach ($field->rules as $rule)
			{
				if (reset($rule) === 'required')
				{
					$required = true;
				}
			}
		}

		switch($field->type)
		{
			case 'hidden':
				$build_field = static::hidden($field);
				break;
			case 'radio': case 'checkbox':
				if (isset($field->options))
				{
					$build_field = array();
					$attributes = $field->attributes;
					$i = 0;
					foreach ($field->options as $value => $label)
					{
						$attributes['name'] = $field->name;
						$field->type == 'checkbox' && $attributes['name'] .= '['.$i.']';

						$attributes['value'] = $value;
						$attributes['label'] = $label;

						if (empty($attributes['id']) && $this->get_config('auto_id', false) == true)
						{
							$attributes['id'] = $this->get_config('auto_id_prefix', '').$field->name.'_'.$i;
						}
						elseif( ! empty($attributes['id']))
						{
							$attributes['id'] .= '_'.$i;
						}

						$build_tag[static::label($label, @$attributes['id'])] = $field->type == 'radio'
							? static::radio($attributes)
							: static::checkbox($attributes);
					}
				}
				else
				{
					$build_field = $field->type == 'radio'
						? static::radio($field->name, $field->value, $field->attributes)
						: static::checkbox($field->name, $field->value, $field->attributes);
				}
				break;
			case 'select':
				$build_field = static::select($field->name, $field->value, $field->options, $field->attributes);
				break;
			case 'textarea':
				$build_field = static::textarea($field->name, $field->value, $field->attributes);
				break;
			default:
				$build_field = static::input($field->name, $field->value, $field->attributes);
				break;
		}

		$output = $field->type != 'hidden' ? $this->field_template($build_field, $field, $required) : "\t\t".$build_field.PHP_EOL;

		return $output;
	}

	/**
	 * Allows for templating fields
	 *
	 * @param	string
	 * @param	Fieldset_Field
	 * @param	bool
	 * @return	string
	 */
	protected function field_template($build_field, Fieldset_Field $field, $required)
	{
		$required_mark = $required ? $this->get_config('required_mark', null) : null;

		if (is_array($build_field))
		{
			$template = $field->template ?: $this->get_config('multi_field_template', null);
			if ($template && preg_match('#\{fields\}(.*)\{fields\}#uD', $template, $match) > 0)
			{
				$build_fields = '';
				foreach ($build_field as $label => $bf)
				{
					$bf_temp = str_replace('{field}', $bf, $match[1]);
					$bf_temp = str_replace('{label}', $label, $bf_temp);
					$build_fields .= $bf_temp;
				}
				$template = str_replace($match[1], $build_fields, $template);
				if ($required_mark)
				{
					$template = str_replace('{required}', $required_mark, $template);
				}
				return $template;
			}

			// still here? wasn't a multi field template available, try the normal one with imploded $build_field
			$build_field = implode(' ', $build_field);
		}

		$label = $field->label ? static::label($field->label, $field->get_attribute('id', null)) : '';
		$template = $field->template ?: $this->get_config('field_template', "\t\t\t{label} {field}\n");
		$template = str_replace('{field}', $build_field, $template);
		$template = str_replace('{label}', $label, $template);
		if ($required_mark)
		{
			$template = str_replace('{required}', $required_mark, $template);
		}
		return $template;
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
		$this->fieldset->set_config($config, $value);

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
			return $this->fieldset->get_config();
		}

		if (is_array($key))
		{
			$output = array();
			foreach ($key as $k)
			{
				$output[$k] = $this->fieldset->get_config($k, null) === null
							? $this->fieldset->get_config($k, $default)
							: static::get_class_config($k, $default);
			}
			return $output;
		}

		return $this->fieldset->get_config($key, null) === null
			? $this->fieldset->get_config($key, $default)
			: static::get_class_config($key, $default);
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

	/**
	 * Alias for $this->fieldset->repopulate() for this fieldset
	 */
	public function repopulate()
	{
		$this->fieldset->repopulate();
	}
}

/* End of file form.php */