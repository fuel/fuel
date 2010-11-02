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
	
	/**
	 * Anchor
	 *
	 * Creates an html link
	 *
	 * @access	public
	 * @param	string	The URL
	 * @param	string	The text value
	 * @param	array	The attributes array
	 * @return	string	The html link
	 */ 
	public static function anchor($href, $text, $attributes = NULL)
	{
		if (substr($href, 0, 4) == 'www.')
			$href = 'http://'.$href;
			
		if (substr($href, 0, 7) != 'http://' && substr($href, 0, 8) != 'https://')
			$href = Config::get('base_url').$href;
		
		return '<a href="'.$href.'"'.self::attr($attributes).'>'.$text.'</a>';
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Mailto
	 *
	 * Creates a mailto link
	 *
	 * @access	public
	 * @param	string	The email address
	 * @param	string	The text value
	 * @param 	string	The subject
	 * @return	string	The mailto link
	 */
	public static function mailto($email, $text, $subject = NULL) 
	{
		if ($subject != '')
			$subject = '?subject='.$subject;
		
		return '<a href="mailto:'.$email.$subject.'">'.$text.'</a>';
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Mailto safe
	 *
	 * Creates a mailto link with Javascript to prevent
	 * bots from picking up the email address
	 *
	 * @access	public
	 * @param	string	The email address
	 * @param	string	The text value
	 * @param 	string	The subject
	 * @return	string	The javascript code containg email
	 */
	public static function mailto_safe($email, $text, $subject = NULL) 
	{
		$email = explode("@", $email);
		if ($subject != NULL)
			$subject = '?subject='.$subject;
		
		$output = '<script type="text/javascript">';
		$output .= 'var user = "'.$email[0].'";';
		$output .= 'var at = "@";';
		$output .= 'var server = "'.$email[1].'";';
		$output .= 'document.write("<a href=" + "mail" + "to:" + user + at + server + "'.$subject.'>'.$text.'</a>");';
		$output .= '</script>';
		return $output;
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Friendly title
	 *
	 * Converts your text to a URL-friendly title
	 * so it can be used in the URL
	 *
	 * @access	public
	 * @param	string	The text
	 * @param	string	The separator (either - or _)
	 * @return	string	The URL-friendly title
	 */
	public function friendly_title($title, $sep = '-') 
	{
		if ($sep != '-' && $sep != '_')
			$sep = '-'; // default
		
		$title = preg_replace('#[^a-zA-Z0-9 ]#', '', $title);
		$title = str_replace(" ", $sep, $title);
		return $title;
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * Prep
	 *
	 * Adds http:// (if it doesn't exist) to the URL
	 *
	 * @access	public
	 * @param	string	The url
	 * @return	string	The URL with http://
	 */
	public static function prep($url) 
	{
		if (substr($url, 0, 7) != 'http://')
			$url = 'http://'.$url;
		
		return $url;
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