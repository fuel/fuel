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
 * Date Class
 *
 * DateTime replacement that supports internationalization and does correction to GMT
 * when your webserver isn't configured correctly.
 *
 * @package		Fuel
 * @subpackage	Core
 * @category	Core
 * @author		Jelmer Schreuder
 *
 * Notes:
 * - Always returns DateTime objects, will accepts both DateTime objects and UNIX timestamps
 * - create_time() only works in non-Windows environments
 * - Uses strftime formatting for dates http://nl2.php.net/manual/en/function.strftime.php
 */
class Fuel_Date {

	/* ---------------------------------------------------------------------------
	 * STATIC PROPERTIES
	 * --------------------------------------------------------------------------- */

	/**
	 * @var int server's time() offset from gmt in seconds
	 */
	protected static $server_gmt_offset = 0;

	/**
	 * @var string default timezone offset from gmt in hours
	 */
	protected static $default_timezone = 'UTC';

	/**
	 * @var string default pattern for date output
	 */
	protected static $default_pattern = 'human';

	/**
	 * @var array rewritten timezones array
	 */
	protected static $php_timezones = array();

	/**
	 * @var array Allows for using numeric output as timezone input
	 */
	protected static $offset_to_timezone = array();

	/* ---------------------------------------------------------------------------
	 * DYNAMIC PROPERTIES
	 * --------------------------------------------------------------------------- */

	/**
	 * @var int instance timestamp
	 */
	protected $timestamp;

	/**
	 * @var double output timezone
	 */
	protected $timezone;

	/* ---------------------------------------------------------------------------
	 * STATIC METHODS
	 * --------------------------------------------------------------------------- */

	public static function _init()
	{
		Date::$server_gmt_offset	= Config::get('server_gmt_offset', 0);
		Date::$default_timezone		= Config::get('default_timezone', 'UTC');

		// Allow the default timezone to be set numericly
		if ( ! is_string(Date::$default_timezone))
		{
			Date::list_timezones();
			$offset = (int) Date::$default_timezone * 3600;
			Date::$default_timezone = Date::$offset_to_timezone[$offset];
		}

		// Ugly temporary windows fix because windows doesn't support strptime()
		// Better fix will accept custom pattern parsing but only parse numeric input on windows servers
		if ( ! function_exists('strptime'))
		{
			function strptime($input, $format)
			{
				$ts = strtotime($input);
				return array(
					'tm_year'	=> date('Y', $ts),
					'tm_month'	=> date('n', $ts),
					'tm_day'	=> date('G', $ts),
					'tm_hour'	=> date('H', $ts),
					'tm_min'	=> date('i', $ts),
					'tm_sec'	=> date('s', $ts)
				);
				// This really is some fugly code, but someone at PHP HQ decided strptime should
				// output this awfull array instead of a timestamp LIKE EVERYONE ELSE DOES!!!
			}
		}
	}

	public function factory($timestamp = null, $timezone = null)
	{
		$timestamp	= is_null($timestamp) ? Date::time() : $timestamp;
		$timezone	= is_null($timezone) ? Date::$default_timezone : $timezone;

		return new Date($timestamp, $timezone);
	}

	/**
	 * Returns the current time with offset
	 * 
	 * @return Date
	 */
	public static function time()
	{
		return new Date(time() + Date::$server_gmt_offset);
	}

	/**
	 * Uses the date config file to translate string input to timestamp
	 *
	 * @param	string			date/time input
	 * @param	string			key name of pattern in config file
	 * @return	Date
	 */
	public static function create_from_string($input, $pattern_key = 'human')
	{
		Config::load('date', 'date');

		$pattern = Config::get('date.patterns.'.$pattern_key, null);
		$pattern = ($pattern === null) ? $pattern_key : $pattern;

		$time = strptime($input, $pattern);
		if ($time === false)
		{
			trigger_error('Input was not recognized by pattern.', E_USER_WARNING);
			return false;
		}
		$date = new Date(mktime($time['tm_hour'], $time['tm_min'], $time['tm_sec'],
						$time['tm_mon'], $time['tm_mday'], $time['tm_year']) + Date::$gmt_offset);

		return Date::factory($date);
	}

	/**
	 * Fetches an array of DateTime objects per interval within a range
	 *
	 * @param	int|Date	start of the range
	 * @param	int|Date	end of the range
	 * @param	int|string	Length of the interval in seconds or valid strtotime time difference
	 * @return	array		array of DateTime objects
	 */
	public static function range_to_array($start, $end, $interval = '+1 Day')
	{
		$start		= ( ! $start instanceof Date) ? Date::factory($start) : $start;
		$end		= ( ! $end instanceof Date) ? Date::factory($end) : $end;
		$interval	= (is_int($interval)) ? $interval : strtotime($interval);

		if ($interval <= 0)
		{
			trigger_error('Input was not recognized by pattern.', E_USER_WARNING);
			return false;
		}

		$range		= array();
		$current	= $start;
		while ($current->get_timestamp() <= $end->get_timestamp())
		{
			$range[] = $current;
			$current = Date::factory($current->get_timestamp() + $interval);
		}

		return $range;
	}

	/**
	 * Returns the number of days in the requested month
	 * (Based on CodeIgniter function)
	 *
	 * @param	int	month as a number (1-12)
	 * @param	int	the year, leave empty for current
	 * @return	int	the number of days in the month
	 */
	public static function days_in_month($month, $year = null)
	{
		$year	= ! empty($year) ? (int) $year : (int) strftime('%G');
		$month	= (int) $month;

		if ($month < 1 || $month > 12)
		{
			trigger_error('Invalid input for month given.', E_USER_WARNING);
			return false;
		}
		elseif ($month == 2)
		{
			if ($year % 400 == 0 || ($year % 4 == 0 && $year % 100 != 0))
			{
				return 29;
			}
		}

		$days_in_month	= array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
		return $days_in_month[$month];
	}

	/**
	 * Returns an array of timezones, rewrites build in output to something usable
	 *
	 * @return array
	 */
	public static function list_timezones()
	{
		if ( ! empty( Date::$php_timezones ) )
		{
			return Date::$php_timezones;
		}
		
		$timezones = DateTimeZone::listAbbreviations();
		foreach ($timezones as $area)
		{
			foreach ($area as $country)
			{
				Date::$php_timezones[$country['timezone_id']] = array('offset' => $country['offset'], 'dst' => $country['dst']);

				if ( ! array_key_exists($country['offset'], Date::$offset_to_timezone))
				{
					Date::$offset_to_timezone[$country['offset']] = $country['timezone_id'];
				}
			}
		}

		return Date::$php_timezones;
	}

	/**
	 * Takes a PHP named timezone and returns its offset in seconds
	 * 
	 * @param string $timezone
	 * @return int
	 */
	public static function timezone_offset($timezone)
	{
		Date::list_timezones();

		return Date::$php_timezones[$timezone];
	}

	/* ---------------------------------------------------------------------------
	 * DYNAMIC METHODS
	 * --------------------------------------------------------------------------- */

	protected function __construct($timestamp, $timezone)
	{
		$this->timestamp = $timestamp;
		$this->set_timezone($timezone);
	}

	/**
	 * Returns the date formatted according to the current locale
	 * 
	 * @param	int|DateTime	time to display
	 * @param	string			either a named pattern from date config file or a pattern
	 * @return	string
	 */
	public function format($pattern_key = 'human')
	{
		Config::load('date', 'date');

		$pattern = Config::get('date.patterns.'.$pattern_key, null);
		$pattern = ($pattern === null) ? $pattern_key : $pattern;

		return strftime($pattern, $this->timestamp);
	}

	/**
	 * Returns the internal timestamp
	 * 
	 * @return int
	 */
	public function get_timestamp()
	{
		return $this->timestamp;
	}

	/**
	 * Returns the internal timezone
	 *
	 * @return string|double
	 */
	public function get_timezone($numeric = false)
	{
		if ($numeric)
		{
			return round((double) Date::timezone_offset($this->timezone) / 3600, 1);
		}
		else
		{
			return $this->timezone;
		}
	}

	/**
	 * Change the timezone
	 *
	 * @param	string|int|double	timezone as offset in hours or PHP named
	 */
	public function set_timezone($timezone)
	{
		if (is_string($timezone))
		{
			$this->timezone = $timezone;
		}
		else
		{
			$offset = (int) $timezone * 3600;
			$this->timezone = Date::$offset_to_timezone[$offset];
		}
	}
}

/* End of file date.php */