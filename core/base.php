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


/**
 * Takes an array of attributes and turns it into a string for an html tag
 *
 * @param	array	$attr
 * @return	string
 */
if ( ! function_exists('html_tag'))
{
	function array_to_attr($attr)
	{
		$attr_str = '';

		if ( ! is_array($attr))
		{
			$attr = (array) $attr;
		}

		foreach ($attr as $property => $value)
		{
			// If the key is numeric then it must be something like selected="selected"
			if (is_numeric($property))
			{
				$property = $value;
			}

			if (in_array($property, array('value', 'alt', 'title')))
			{
				$value = htmlentities($value, ENT_QUOTES, INTERNAL_ENC);
			}
			$attr_str .= $property.'="'.$value.'" ';
		}

		// We strip off the last space for return
		return trim($attr_str);
	}
}

/**
 * Create a XHTML tag
 *
 * @param	string	The tag name
 * @param	array	The tag attributes
 * @param	string	The content to place in the tag
 * @return	string
 */
if ( ! function_exists('html_tag'))
{
	function html_tag($tag, $attr = array(), $content = '')
	{
		$html = '<'.$tag;

		$html .= ( ! empty($attr)) ? ' '.array_to_attr($attr) : '';
		$html .= empty($content) ? ' />' : '>';
		$html .= ( ! empty($content)) ? $content.'</'.$tag.'>' : '';

		return $html;
	}
}

/**
 * A case-insensitive version of in_array.
 *
 * @param	mixed	$needle
 * @param	array	$haystack
 * @return	bool
 */
if ( ! function_exists('in_arrayi'))
{
	function in_arrayi($needle, $haystack)
	{
		return in_array(strtolower($needle), array_map('strtolower', $haystack));
	}
}

/**
 * Render's a view and returns the output.
 *
 * @param	string	The view name/path
 * @param	array	The data for the view
 * @return	string
 */
if ( ! function_exists('render'))
{
	function render($view, $data = array())
	{
		return View::factory($view, $data)->render();
	}
}

/* End of file base.php */