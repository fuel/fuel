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

// ------------------------------------------------------------------------

/**
 * Validation error
 *
 * Contains all the information about a validation error
 *
 * @package		Fuel
 * @subpackage	Core
 * @category	Core
 * @author		Jelmer Schreuder
 */
class Validation_Error extends Exception {
	public $field = '';
	public $value = '';
	public $callback = '';
	public $params = array();

	/**
	 * Constructor
	 *
	 * @param	array		Validation field description
	 * @param	mixed		Unvalidated value
	 * @param	callback	Failed rule callback
	 * @param	array		Failed rule callback params
	 */
	public function __construct($field, $value, $callback, $params)
	{
		$this->field = $field;
		$this->value = $value;
		$this->params = $params;

		$this->callback = is_string($callback) ? str_replace('::', ':', $callback) : get_class($callback[0]).':'.$callback[1];
	}

	/**
	 * Get Message
	 *
	 * Shows the error message which can be taken from loaded language file.
	 *
	 * @param	string	Message to use, or false to try and load it from Lang class
	 * @param	string	HTML to prefix error message
	 * @param	string	HTML to postfix error message
	 * @return	string
	 */
	public function get_message($msg = false, $open = '', $close = '')
	{
		$msg = is_null($msg) ? __('validation.'.$this->callback) : $msg;
		if ($msg == false)
		{
			return $open.'Validation rule '.$this->callback.' failed for '.$this->field['title'].$close;
		}

		// to safe some performance when there are no variables in the $msg
		if (strpos(':', $msg) !== false)
		{
			return $msg;
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

		return $open.__('validation.'.$this->callback, $replace).$close;
	}

	public function __toString()
	{
		return $this->get_message();
	}
}

/* End of file validation.php */