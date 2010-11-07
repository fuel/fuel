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

class Fuel_URL {

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
		return '<a href="'.URL::href($href).'"'.URL::attr($attributes).'>'.$text.'</a>';
	}

	/**
	 * Href
	 *
	 * Creates an html link
	 *
	 * @access	public
	 * @param	string	The URL
	 */
	public static function href($href)
	{
		if ( ! preg_match('#^\w+://# i', $href))
		{
			$url = Config::get('base_url');

			if (Config::get('index_file'))
			{
				$url .= Config::get('index_file').'/';
			}

			$href = $url.$href;
		}

		return $href;
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
	public static function mail_to($email, $text = NULL, $subject = NULL)
	{
		$text or $text = $email;

		$subject and $subject = '?subject='.$subject;

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
	public static function mail_to_safe($email, $text, $subject = NULL)
	{
		$text or $text = str_replace('@', '[at]', $email);

		$email = explode("@", $email);
		
		$subject and $subject = '?subject='.$subject;
		
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
	public function friendly_title($str, $sep = '-', $lowercase = FALSE)
	{
		if ($sep != '-' && $sep != '_')
		{
			$sep = '-'; // default
		}
		
        $from = array('ă','á','à','â','ã','ª','Á','À',
          'Â','Ã', 'é','è','ê','É','È','Ê','í','ì','î','Í',
          'Ì','Î','ò','ó','ô', 'õ','º','Ó','Ò','Ô','Õ','ş','Ş'
          ,'ţ','Ţ','ú','ù','û','Ú','Ù','Û','ç','Ç','Ñ','ñ');
        $to = array('a','a','a','a','a','a','A','A',
          'A','A','e','e','e','E','E','E','i','i','i','I','I',
          'I','o','o','o','o','o','O','O','O','O','s','S',
          't','T','u','u','u','U','U','U','c','C','N','n');
		
        $str = trim(str_replace($from, $to, $str));

        $trans = array(
			'&\#\d+?;' => '',
			'&\S+?;' => '',
			'\s+' => $sep,
			'[^a-zАБВГҐДЕЄЁЖЗИІЫЇЙКЛМНОПРСТУФХЦЧШЩэЮЯЬЪабвгґдеєёжзиіыїйклмнопрстуфхцчшщюяьъ0-9\-\._]' => '',
			$sep . '+' => $sep,
			$sep . '$' => $sep,
			'^' . $sep => $sep,
			'\.+$' => ''
		);

        $str = strip_tags($str);

        foreach ($trans as $key => $val)
        {
            $str = preg_replace("#".$key."#i", $val, $str);
        }

        if ($lowercase === TRUE)
        {
        	$str = function_exists('mb_convert_case')
				? mb_convert_case($str, MB_CASE_LOWER, 'UTF-8')
				: strtolower($str);
        }

        return trim(stripslashes($str));
    }

	// --------------------------------------------------------------------------

	/**
	 * Prep
	 *
	 * Adds the http:// schema to the URL if missing
	 *
	 * @access	public
	 * @param	string	The url
	 * @return	string	The URL with http://
	 */
	public static function prep($url)
	{
		if ( ! preg_match('#^\w+://# i', $url))
		{
			$url = 'http://'.$url;
		}

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