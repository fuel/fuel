<?php
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

namespace Fuel;

use Fuel\Application as App;

class Url {

	/**
	 * Creates an html link
	 *
	 * @param	string	the url
	 * @param	string	the text value
	 * @param	array	the attributes array
	 * @return	string	the html link
	 */
	public static function anchor($href, $text, $attributes = array())
	{
		$attributes['href'] = static::href($href);
		return html_tag('a', $attributes, $text);
	}

	// --------------------------------------------------------------------

	/**
	 * Creates a url with the given uri, including the base url
	 *
	 * @param	string	the url
	 * @param	array	some variables for the url
	 */
	public static function href($href, $variables = array())
	{
		if ( ! preg_match('#^\w+://# i', $href))
		{
			$url = App\Config::get('base_url');

			if (App\Config::get('index_file'))
			{
				$url .= App\Config::get('index_file').'/';
			}

			$href = $url.ltrim($href, '/');
		}

		foreach($variables as $key => $val)
		{
			$href = str_replace(':'.$key, $val, $href);
		}

		return $href;
	}

	// --------------------------------------------------------------------

	/**
	 * Gets tje current URL, including the BASE_URL
	 *
	 * @param	string	the url
	 */
	public static function current()
	{
		return static::href(Request::active()->uri->uri);
	}

	/**
	 * Creates a mailto link.
	 *
	 * @param	string	The email address
	 * @param	string	The text value
	 * @param	string	The subject
	 * @return	string	The mailto link
	 */
	public static function mail_to($email, $text = NULL, $subject = NULL, $attr = array())
	{
		$text or $text = $email;

		$subject and $subject = '?subject='.$subject;

		return html_tag('a', array(
			'href' => 'mailto:'.$email.$subject,
		) + $attr, $text);
	}

	/**
	 * Creates a mailto link with Javascript to prevent bots from picking up the
	 * email address.
	 *
	 * @param	string	the email address
	 * @param	string	the text value
	 * @param	string	the subject
	 * @param	array	attributes for the tag
	 * @return	string	the javascript code containg email
	 */
	public static function mail_to_safe($email, $text, $subject = null, $attr = array())
	{
		$text or $text = str_replace('@', '[at]', $email);

		$email = explode("@", $email);
		
		$subject and $subject = '?subject='.$subject;
		
		$attr = array_to_attr($attr);
		$attr = ($attr == '' ? '' : ' ').$attr;
		
		$output = '<script type="text/javascript">';
		$output .= 'var user = "'.$email[0].'";';
		$output .= 'var at = "@";';
		$output .= 'var server = "'.$email[1].'";';
		$output .= "document.write('<a href=\"' + 'mail' + 'to:' + user + at + server + '$subject\"$attr>$text</a>');";
		$output .= '</script>';
		return $output;
	}

	/**
	 * Converts your text to a URL-friendly title so it can be used in the URL.
	 *
	 * @param	string	the text
	 * @param	string	the separator (either - or _)
	 * @return	string	the new title
	 */
	public static function friendly_title($str, $sep = '-', $lowercase = false)
	{
		if ($sep != '-' && $sep != '_')
		{
			$sep = '-'; // default
		}
		
		$from = array(
			'ă','á','à','â','ã','ª','Á','À',
			'Â','Ã', 'é','è','ê','É','È','Ê','í','ì','î','Í',
			'Ì','Î','ò','ó','ô', 'õ','º','Ó','Ò','Ô','Õ','ş','Ş',
			'ţ','Ţ','ú','ù','û','Ú','Ù','Û','ç','Ç','Ñ','ñ'
		);
		$to = array(
			'a','a','a','a','a','a','A','A',
			'A','A','e','e','e','E','E','E','i','i','i','I','I',
			'I','o','o','o','o','o','O','O','O','O','s','S',
			't','T','u','u','u','U','U','U','c','C','N','n'
		);
		
		$str = trim(str_replace($from, $to, $str));

		$trans = array(
			'&\#\d+?;' => '',
			'&\S+?;' => '',
			'\s+' => $sep,
			'[^a-zАБВГҐДЕЄЁЖЗИІЫЇЙКЛМНОПРСТУФХЦЧШЩэЮЯЬЪабвгґдеєёжзиіыїйклмнопрстуфхцчшщюяьъ0-9\-\._]' => '',
			$sep.'+' => $sep,
			$sep.'$' => $sep,
			'^'.$sep => $sep,
			'\.+$' => ''
		);

		$str = App\Security::strip_tags($str);

		foreach ($trans as $key => $val)
		{
			$str = preg_replace("#".$key."#i", $val, $str);
		}

		if ($lowercase === true)
		{
			$str = function_exists('mb_convert_case')
				? mb_convert_case($str, MB_CASE_LOWER, 'UTF-8')
				: strtolower($str);
		}

		return trim(stripslashes($str));
	}

	/**
	 * Adds the given schema to the given URL if it is not already there.
	 *
	 * @param	string	the url
	 * @param	string	the schema
	 * @return	string	url with schema
	 */
	public static function prep($url, $schema = 'http')
	{
		if ( ! preg_match('#^\w+://# i', $url))
		{
			$url = $schema.'://'.$url;
		}

		return $url;
	}
}

/* End of file url.php */