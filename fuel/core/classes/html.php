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
	 * @param	int				1 through 6 for h1-h6
	 * @param	array|string	tag attributes
	 * @param	string			heading text
	 * @return	string
	 */
	public static function h($num = 1, $attr = false, $content = '')
	{
		return html_tag('h'.$num, $attr, $content);
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
	public static function title($content = false)
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
	public static function meta($name, $content = '', $type = 'name')
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
		Config::load('doctypes', true);
		static::$doctypes = Config::get('doctypes');
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
	public static function header($content, $attr = array())
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
	public static function audio($src = array(), $attr = '')
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
	public static function ul($list, $style = false)
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
	public static function ol($list, $style = false)
	{
		return static::build_list('ol', $list, $style);
	}
	
	/**
	 * Generates the html for the list methods
	 *
	 * @param	string	list type (ol or ul)
	 * @param	array	list items, may be nested
	 * @param	array	tag attributes
	 * @return	string
	 */
	protected static function build_list($type = 'ul', $list, $attr = false)
	{
		if ( ! is_array($list))
		{
			$result = false;
		}

		foreach ($list as $key => $val)
		{
			if ( ! is_array($val))
			{
				$out .= html_tag('li', false, $val);
			}
			else
			{
				$out .= html_tag('li', false, $key . static::build_list($type, $val, ''));
			}
		}
		$result = html_tag($type, $attr, $out);
		return $result;
	}
}
/* End of file html.php */
