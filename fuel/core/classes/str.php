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
 * @copyright	2010 - 2011 Fuel Development Team
 * @link		http://fuelphp.com
 */

namespace Fuel\Core;

/**
 * Str Class
 *
 * @package		Fuel
 * @category	String Manipulation
 * @author		Tom Schlick
 * @link		http://fuelphp.com/docs/classes/str.html
 */
class Str {

	/**
	 * Allows strings to be alternated.
	 *
	 * @author	ExpressionEngine Dev Team
	 * @license	http://codeigniter.com/user_guide/license.html
	 *
	 * @access	public
	 * @param	string (as many parameters as needed)
	 * @return	string
	 */
	function alternator()
	{
		static $alternator_i;

		if (func_num_args() == 0)
		{
			$alternator_i = 0;
			return '';
		}
		$args = func_get_args();
		return $args[($alternator_i++ % count($args))];
	}
	
	/**
	 * Converts single and double quotes to entities
	 *
	 * @author	ExpressionEngine Dev Team
	 * @license	http://codeigniter.com/user_guide/license.html
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public static function quotes_to_entities($str)
	{
		return str_replace(array("\'","\"","'",'"'), array("&#39;","&quot;","&#39;","&quot;"), $str);
	}
	
	/**
	 * Creates a random string of characters
	 *
	 * @param	string	the type of string
	 * @param	int		the number of characters
	 * @return	string	the random string
	 */
	public static function random($type = 'alnum', $length = 16)
	{
		switch($type)
		{
			case 'basic': 
				return mt_rand();
			break;
			
			default:
			case 'alnum':
			case 'numeric':
			case 'nozero':
			case 'alpha':
			case 'distinct':
			case 'hexdec':
				switch ($type)
				{
					case 'alpha':
						$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
					break;
					
					default:
					case 'alnum':
						$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
					break;
					
					case 'numeric':
						$pool = '0123456789';
					break;
					
					case 'nozero':
						$pool = '123456789';
					break;
					
					case 'distinct':
        				$pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
    				break;
    				
    				case 'hexdec':
        				$pool = '0123456789abcdef';
    				break;
				}

				$str = '';
				for ($i=0; $i < $length; $i++)
				{
					$str .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
				}
				return $str;
			break;
				
			case 'unique':
				return md5(uniqid(mt_rand()));
			break;
			
			case 'sha1'	:
				return sha1(uniqid(mt_rand(), true));
			break;
		}
	}
	
	/**
	 * Converts double slashes in a string to a single slash,
	 * except those found in http://
	 *
	 * http://www.some-site.com//index.php
	 *
	 * becomes:
	 *
	 * http://www.some-site.com/index.php
	 *
	 * @author	ExpressionEngine Dev Team
	 * @license	http://codeigniter.com/user_guide/license.html
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public static function reduce_double_slashes($str)
	{
		return preg_replace("#(^|[^:])//+#", "\\1/", $str);
	}
	
	/**
	 * Reduces multiple instances of a particular character.  Example:
	 *
	 * Fred, Bill,, Joe, Jimmy
	 *
	 * becomes:
	 *
	 * Fred, Bill, Joe, Jimmy
	 *
	 * @author	ExpressionEngine Dev Team
	 * @license	http://codeigniter.com/user_guide/license.html
	 *
	 * @access	public
	 * @param	string
	 * @param	string	the character you wish to reduce
	 * @param	bool	TRUE/FALSE - whether to trim the character from the beginning/end
	 * @return	string
	 */
	public static function reduce_multiples($str, $character = ',', $trim = false)
	{
		$str = preg_replace('#'.preg_quote($character, '#').'{2,}#', $character, $str);

		if ($trim === true)
		{
			$str = trim($str, $character);
		}

		return $str;
	}

	/**
	 * Repeater function
	 * 
	 * @author	ExpressionEngine Dev Team
	 * @license	http://codeigniter.com/user_guide/license.html
	 *
	 * @access	public
	 * @param	string
	 * @param	integer	number of repeats
	 * @return	string
	 */
	public static function repeater($data, $num = 1)
	{
		return (($num > 0) ? str_repeat($data, $num) : '');
	}
	
	/**
	 * Removes single and double quotes from a string
	 *
	 * @author	ExpressionEngine Dev Team
	 * @license	http://codeigniter.com/user_guide/license.html
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	function strip_quotes($str)
	{
		return str_replace(array('"', "'"), '', $str);
	}
	
	/**
	 * Removes slashes contained in a string or in an array
	 *
	 * @author	ExpressionEngine Dev Team
	 * @license	http://codeigniter.com/user_guide/license.html
	 *
	 * @access	public
	 * @param	mixed	string or array
	 * @return	mixed	string or array
	 */
	function strip_slashes($str)
	{
		if (is_array($str))
		{
			foreach ($str as $key => $val)
			{
				$str[$key] = strip_slashes($val);
			}
		}
		else
		{
			$str = stripslashes($str);
		}

		return $str;
	}

	/**
	 * Removes any leading/trailing slashes from a string:
	 *
	 * /this/that/theother/
	 *
	 * becomes:
	 *
	 * this/that/theother
	 *
	 * @author	ExpressionEngine Dev Team
	 * @license	http://codeigniter.com/user_guide/license.html
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public static function trim_slashes($str)
	{
		return trim($str, '/');
	}
}

/* End of file str.php */