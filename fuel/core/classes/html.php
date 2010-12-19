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

namespace Fuel\Core;

use Fuel\App as App;

// ------------------------------------------------------------------------

/**
* Html Class
 *
 * @package		Fuel
 * @subpackage	Core
 * @category	Core
 * @author		Alfredo Rivera
 */
class Html
{
	public static $doctypes = array();
	public static $html5 = false;

	/**
	 * Generates a html heading tag
	 *
	 * @param	string			heading text
	 * @param	int				1 through 6 for h1-h6
	 * @param	array|string	tag attributes
	 * @return	string
	 */
	public static function h($content = '', $num = 1, $attr = false)
	{
		return html_tag('h'.$num, $attr, $content);
	}

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
		if ( ! preg_match('#^\w+://# i', $href))
		{
			$href = App\Uri::create($href);
		}
		$attributes['href'] = $href;

		return html_tag('a', $attributes, $text);
	}

	/**
	 * Adds the given schema to the given URL if it is not already there.
	 *
	 * @param	string	the url
	 * @param	string	the schema
	 * @return	string	url with schema
	 */
	public static function prep_url($url, $schema = 'http')
	{
		if ( ! preg_match('#^\w+://# i', $url))
		{
			$url = $schema.'://'.$url;
		}

		return $url;
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
	 * Generates a html break tag
	 *
	 * @param	int				number of times to repeat the br
	 * @param	array|string	tag attributes
	 * @return	string
	 */
	public static function br($num = 1, $attr = false)
	{
		return str_repeat(html_tag('br', $attr), $num);
	}

	/**
	 * Generates a html horizontal rule tag
	 *
	 * @param	array|string	tag attributes
	 * @return	string
	 */
	public static function hr($attr = false)
	{
		return html_tag('hr', $attr);
	}

	/**
	 * Generates a html title tag
	 *
	 * @param	string	page title
	 * @return	string
	 */
	public static function title($content = '')
	{
		return html_tag('title', array(), $content);
	}

	/**
	 * Generates a ascii code for non-breaking whitespaces
	 *
	 * @param	int		number of times to repeat
	 * @return	string
	 */
	public static function nbs($num = 1)
	{
		return str_repeat('&nbsp;', $num);
	}

	/**
	 * Generates a html meta tag
	 *
	 * @param	string|array	multiple inputs or name/http-equiv value
	 * @param	string			content value
	 * @param	string			name or http-equiv
	 * @return	string
	 */
	public static function meta($name = '', $content = '', $type = 'name')
	{
		if( ! is_array($name))
		{
			$result = html_tag('meta', array($type => $name, 'content' => $content));
		}
		elseif(is_array($name))
		{
			foreach($name as $array)
			{
				$meta = $array;
				$result .= html_tag('meta', $meta);
			}
		}
		return $result;
	}

	/**
	 * Generates a html doctype tag
	 *
	 * @param	string	doctype declaration key from doctypes config
	 * @return	string
	 */
	public static function doctype($type = 'xhtml1-trans')
	{
		App\Config::load('doctypes', true);
		static::$doctypes = App\Config::get('doctypes');
		if(is_array(static::$doctypes) && isset(static::$doctypes[$type]))
		{
			if($type == "html5")
			{
				static::$html5 = true;
			}
			return static::$doctypes[$type];
		}
		else
		{
			return false;
		}
	}

	/**
	 * Generates a html5 header tag or div with id "header"
	 *
	 * @param	string	header content
	 * @param	array	tag attributes
	 * @return	string
	 */
	public static function header($content = '', $attr = array())
	{
		if(static::$html5)
		{
			return html_tag('header', $attr, $content);
		}
		else
		{
			return html_tag('div', array_merge(array('id' => 'header'), $attr), $content);
		}
	}

	/**
	 * Generates a html5 audio tag
	 * It is required that you set html5 as the doctype to use this method
	 *
	 * @param	string|array	one or multiple audio sources
	 * @param	array			tag attributes
	 * @return	string
	 */
	public static function audio($src = array(), $attr = false)
	{
		if(static::$html5)
		{
			if(is_array($src))
			{
				foreach($src as $item)
				{
					$source .= html_tag('source', array('src' => $item));
				}
			}
			else
			{
				$source = html_tag('source', array('src' => $src));
			}
			return html_tag('audio', $attr, $source);
		}
	}

	/**
	 * Generates a html un-ordered list tag
	 *
	 * @param	array			list items, may be nested
	 * @param	array|string	outer list attributes
	 * @return	string
	 */
	public static function ul(Array $list = array(), $style = false)
	{
		return static::build_list('ul', $list, $style);
	}

	/**
	 * Generates a html ordered list tag
	 *
	 * @param	array			list items, may be nested
	 * @param	array|string	outer list attributes
	 * @return	string
	 */
	public static function ol(Array $list = array(), $style = false)
	{
		return static::build_list('ol', $list, $style);
	}

	/**
	 * Generates the html for the list methods
	 *
	 * @param	string	list type (ol or ul)
	 * @param	array	list items, may be nested
	 * @param	array	tag attributes
	 * @param	string	indentation
	 * @return	string
	 */
	protected static function build_list($type = 'ul', Array $list = array(), $attr = false, $indent = '')
	{
		if ( ! is_array($list))
		{
			$result = false;
		}

		$out = '';
		foreach ($list as $key => $val)
		{
			if ( ! is_array($val))
			{
				$out .= $indent."\t".html_tag('li', false, $val).PHP_EOL;
			}
			else
			{
				$out .= $indent."\t".html_tag('li', false, $key.PHP_EOL.static::build_list($type, $val, '', $indent."\t\t").$indent."\t").PHP_EOL;
			}
		}
		$result = $indent.html_tag($type, $attr, PHP_EOL.$out.$indent).PHP_EOL;
		return $result;
	}
}
/* End of file html.php */
