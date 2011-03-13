<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2011 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Core;

/**
 * Agent class
 *
 * Identifies the platform, browser, robot, or mobile devise of the browsing agent
 *
 * NOTE: This class has been taken from the CodeIgniter framework and slightly modified,
 * but on the whole all credit goes to them. Over time this will be worked on.
 *
 * @package		Fuel
 * @category	Core
 * @author		ExpressionEngine Dev Team
 * @modified	Mike Branderhorst
 * @copyright	(c) 2008 - 2011 EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://fuelphp.com/docs/classes/agent.html
 */
class Agent {

	protected static $is_browser = false;	// \Agent::is_browser()	or \Agent::is_browser('browsername')
	protected static $is_robot	= false;	// \Agent::is_robot()	or \Agent::is_robot('robotname')
	protected static $is_mobile	= false;	// \Agent::is_mobile()	or \Agent::is_mobile('mobilename')

	protected static $agent		= null;		// \Agent::string()
	protected static $platform	= null;		// \Agent::platform()
	protected static $browser	= null;		// \Agent::browser()
	protected static $version	= null;		// \Agent::version()
	protected static $mobile	= null;		// \Agent::mobile()
	protected static $robot		= null;		// \Agent::robot()
	
	protected static $languages	= array();
	protected static $charsets	= array();
	protected static $platforms	= array();
	protected static $browsers	= array();
	protected static $mobiles	= array();
	protected static $robots	= array();

	// ---------------------------------------------------------------------

	/**
	 * @access	public
	 * @return void
	 */
	public static function _init()
	{
		if ($agent = \Input::server('http_user_agent'))
		{
			static::$agent = trim($agent);
			
			if (static::_init_config())
			{
				static::_init_data();
			}
		}
	}

	/**
	 * @access	protected
	 * @return bool
	 */
	protected static function _init_config()
	{
		$config = \Config::load('agent', true);
		
		$return = false;
		
		if ($platforms = \Config::get('agent.platforms'))
		{
			static::$platforms = $platforms;
			unset($platforms);
			$return = true;
		}

		if ($browsers = \Config::get('agent.browsers'))
		{
			static::$browsers = $browsers;
			unset($browsers);
			$return = true;
		}

		if ($mobiles = \Config::get('agent.mobiles'))
		{
			static::$mobiles = $mobiles;
			unset($mobiles);
			$return = true;
		}

		if ($robots = \Config::get('agent.robots'))
		{
			static::$robots = $robots;
			unset($robots);
			$return = true;
		}
		
		return $return;
	}
	
	/**
	 * @access	protected
	 * @return	void
	 */
	protected static function _init_data()
	{
		static::_set_platform();

		foreach (array('_set_browser', '_set_robot', '_set_mobile') as $function)
		{
			if (static::$function() === true)
			{
				break;
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Set the Platform
	 *
	 * @access	protected
	 * @return	mixed
	 */
	protected static function _set_platform()
	{
		if (is_array(static::$platforms) and static::$platforms)
		{
			foreach (static::$platforms as $key => $val)
			{
				if (preg_match("|".preg_quote($key)."|i", static::$agent))
				{
					static::$platform = $val;
					
					return true;
				}
			}
		}

		static::$platform = 'Unknown Platform';
	}

	// --------------------------------------------------------------------

	/**
	 * Set the Browser
	 *
	 * @access	protected
	 * @return	bool
	 */
	protected static function _set_browser()
	{
		if (is_array(static::$browsers) and static::$browsers)
		{
			foreach (static::$browsers as $key => $val)
			{
				if (preg_match("|".preg_quote($key).".*?([0-9\.]+)|i", static::$agent, $match))
				{
					static::$is_browser = true;
					static::$version = $match[1];
					static::$browser = $val;
					static::_set_mobile();

					return true;
				}
			}
		}

		return false;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the Robot
	 *
	 * @access	protected
	 * @return	bool
	 */
	protected static function _set_robot()
	{
		if (is_array(static::$robots) and static::$robots)
		{
			foreach (static::$robots as $key => $val)
			{
				if (preg_match("|".preg_quote($key)."|i", static::$agent))
				{
					static::$is_robot = true;
					static::$robot = $val;

					return true;
				}
			}
		}

		return false;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the Mobile Device
	 *
	 * @access	protected
	 * @return	bool
	 */
	protected static function _set_mobile()
	{
		if (is_array(static::$mobiles) and static::$mobiles)
		{
			foreach (static::$mobiles as $key => $val)
			{
				if (stripos(static::$agent, $key) !== false)
				{
					static::$is_mobile = true;
					static::$mobile = $val;

					return true;
				}
			}
		}

		return false;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the accepted languages
	 *
	 * @access	protected
	 * @return	void
	 */
	protected static function _set_languages()
	{
		if (empty(static::$languages) and $language = \Input::server('http_accept_language'))
		{
			$languages = preg_replace('/(;q=[0-9\.]+)/i', '', strtolower(trim($language)));

			static::$languages = explode(',', $languages);
		}

		empty(static::$languages) and static::$languages = array('Undefined');
	}

	// --------------------------------------------------------------------

	/**
	 * Set the accepted character sets
	 *
	 * @access	protected
	 * @return	void
	 */
	protected static function _set_charsets()
	{
		if (empty(static::$charsets) and $charset = \Input::server('http_accept_charset'))
		{
			$charsets = preg_replace('/(;q=.+)/i', '', strtolower(trim($charset)));

			static::$charsets = explode(',', $charsets);
		}
		
		empty(static::$charsets) and static::$charsets = array('Undefined');
	}

	// --------------------------------------------------------------------

	/**
	 * Is Browser ?
	 *
	 * @param mixed $browser optional, check (one of) if given browsername(s) is true
	 * @access public
	 * @return bool
	 */
	public static function is_browser($browser = null)
	{
		if ( ! static::$is_browser)
		{
			return false;
		}

		if (empty($browser))
		{
			return true;
		}

		is_array($browser) or $browser = array($browser);

		foreach ($browser as $b)
		{
			if (array_key_exists($b, static::$browsers) and static::$browser === static::$browsers[$b]) return true;
		}

		return false;
	}

	// --------------------------------------------------------------------

	/**
	 * Is Robot ?
	 *
	 * @param mixed $robot optional, check (one of) if given robotname(s) is true
	 * @access public
	 * @return bool
	 */
	public static function is_robot($robot = null)
	{
		if ( ! static::$is_robot)
		{
			return false;
		}

		if (empty($robot))
		{
			return true;
		}

		is_array($robot) or $robot = array($robot);

		foreach ($robot as $r)
		{
			if (array_key_exists(strtolower($r), static::$robots) and static::$robot === static::$robots[strtolower($r)]) return true;
		}

		return false;
	}

	// --------------------------------------------------------------------

	/**
	 * Is Mobile ?
	 *
	 * @param mixed $mobile optional, check (one of) if given mobilename(s) is true
	 * @access public
	 * @return bool
	 */
	public static function is_mobile($mobile = null)
	{
		if ( ! static::$is_mobile)
		{
			return false;
		}

		if (empty($mobile))
		{
			return true;
		}

		is_array($mobile) or $mobile = array($mobile);

		foreach ($mobile as $m)
		{
			if (array_key_exists(strtolower($m), static::$mobiles) and static::$mobile === static::$mobiles[strtolower($m)]) return true;
		}

		return false;
	}

	// --------------------------------------------------------------------

	/**
	 * Is referral ?
	 *
	 * @access	public
	 * @return	bool
	 */
	public static function is_referral()
	{
		return (\Input::server('http_referer')) ? true : false;
	}

	public static function is_referrer()
	{
		return static::is_referral();
	}

	public static function is_referer()
	{
		return static::is_referral();
	}

	// --------------------------------------------------------------------

	/**
	 * String
	 *
	 * @access	public
	 * @return	string
	 */
	public static function string()
	{
		return static::$agent;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Platform
	 *
	 * @access	public
	 * @return	string
	 */
	public static function platform()
	{
		return static::$platform;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Browser Name
	 *
	 * @access	public
	 * @return	string
	 */
	public static function browser()
	{
		return static::$browser;
	}

	// --------------------------------------------------------------------

	/**
	 * Get the Browser Version
	 *
	 * @access	public
	 * @return	string
	 */
	public static function version()
	{
		return static::$version;
	}

	// --------------------------------------------------------------------

	/**
	 * Get The Robot Name
	 *
	 * @access	public
	 * @return	string
	 */
	public static function robot()
	{
		return static::$robot;
	}
	// --------------------------------------------------------------------

	/**
	 * Get the Mobile Device
	 *
	 * @access	public
	 * @return	string
	 */
	public static function mobile()
	{
		return static::$mobile;
	}

	// --------------------------------------------------------------------

	/**
	 * Get the referrer
	 *
	 * @access	public
	 * @return	string
	 */
	public static function referrer()
	{
		return ($referrer = \Input::server('http_referer')) ? trim($referrer) : null;
	}

	public static function referer()
	{
		return static::referrer();
	}

	// --------------------------------------------------------------------

	/**
	 * Get the accepted languages
	 *
	 * @access	public
	 * @return	array
	 */
	public static function languages()
	{
		if (empty(static::$languages))
		{
			static::_set_languages();
		}
		
		return static::$languages;
	}

	// --------------------------------------------------------------------

	/**
	 * Get the accepted Character Sets
	 *
	 * @access	public
	 * @return	array
	 */
	public static function charsets()
	{
		if (empty(static::$charsets))
		{
			static::_set_charsets();
		}

		return static::$charsets;
	}

	// --------------------------------------------------------------------

	/**
	 * Test for a particular language
	 *
	 * @access	public
	 * @return	bool
	 */
	public static function accept_language($language = 'en')
	{
		return (in_array(strtolower($language), static::languages(), true)) ? true : false;
	}

	// --------------------------------------------------------------------

	/**
	 * Test for a particular character set
	 *
	 * @access	public
	 * @return	bool
	 */
	public static function accept_charset($charset = 'utf-8')
	{
		return (in_array(strtolower($charset), static::charsets(), true)) ? true : false;
	}

}

/* End of file agent.php */