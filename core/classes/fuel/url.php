<?php defined('COREPATH') or exit('No direct script access allowed');

/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Dan Horrigan <http://dhorrigan.com>
 * @license		Apache License v2.0
 * @copyright	2010 Dan Horrigan
 */

class Fuel_Url {

	public static function anchor($href, $text, $attributes='')
	{
		return '<a href="'.$href.'"'.self::attr($attributes).'>'.$text.'</a>';
	}
	
	// --------------------------------------------------------------------

	/**
	 * Attr
	 *
	 * Converts an array of attribute into a string
	 *
	 * @access	public
	 * @param	array	The attribute array
	 * @return	string	The attribute string
	 * @return	string
	 */
	public static function attr($attributes = NULL)
	{
		if (empty($attributes))
		{
			return '';
		}

		$final = '';
		foreach ($attributes as $key => $value)
		{
			if ($value === NULL)
			{
				continue;
			}

			$final .= ' '.$key.'="'.htmlspecialchars($value, ENT_QUOTES).'"';
		}

		return $final;
	}
}

/* End of file url.php */