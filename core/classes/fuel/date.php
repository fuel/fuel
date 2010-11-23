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
 * @copyright	2010 Dan Horrigan
 * @link		http://fuelphp.com
 */

namespace Fuel;

// ------------------------------------------------------------------------

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
 * - Always returns Date objects, will accept both Date objects and UNIX timestamps
 * - create_time() uses strptime and has currently a very bad hack to use strtotime for windows servers
 * - Uses strftime formatting for dates http://www.php.net/manual/en/function.strftime.php
 */
class Date {

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
	 * @var array Allows for using numeric timezone input
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
		static::$server_gmt_offset	= Config::get('server_gmt_offset', 0);
		static::$default_timezone		= Config::get('default_timezone', 'UTC');

		// Allow the default timezone to be set numericly
		if ( ! is_string(static::$default_timezone))
		{
			static::$default_timezone = static::offset_to_timezone(static::$default_timezone);
		}

		// Set the default timezone
		date_default_timezone_set(static::$default_timezone);

		// Ugly temporary windows fix because windows doesn't support strptime()
		// Better fix will accept custom pattern parsing but only parse numeric input on windows servers
		if ( ! function_exists('strptime'))
		{
			function strptime($input, $format)
			{
				$ts = strtotime($input);
				return array(
					'tm_year'	=> date('Y', $ts),
					'tm_mon'	=> date('n', $ts),
					'tm_mday'	=> date('j', $ts),
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
		$timestamp	= is_null($timestamp) ? time() + static::$server_gmt_offset : $timestamp;
		$timezone	= is_null($timezone) ? static::$default_timezone : $timezone;

		return new Date($timestamp, $timezone);
	}

	/**
	 * Returns the current time with offset
	 * 
	 * @return Date
	 */
	public static function time($timezone = null)
	{
		return static::factory(null, $timezone);
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
		$timestamp = mktime($time['tm_hour'], $time['tm_min'], $time['tm_sec'],
						$time['tm_mon'], $time['tm_mday'], $time['tm_year']);
		
		return static::factory($timestamp + static::$server_gmt_offset);
	}

	/**
	 * Fetches an array of Date objects per interval within a range
	 *
	 * @param	int|Date	start of the range
	 * @param	int|Date	end of the range
	 * @param	int|string	Length of the interval in seconds or valid strtotime time difference
	 * @return	array		array of Date objects
	 */
	public static function range_to_array($start, $end, $interval = '+1 Day')
	{
		$start		= ( ! $start instanceof Date) ? static::factory($start) : $start;
		$end		= ( ! $end instanceof Date) ? static::factory($end) : $end;
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
			$current = static::factory($current->get_timestamp() + $interval);
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
		$year	= ! empty($year) ? (int) $year : (int) date('Y');
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
		if ( ! empty(static::$php_timezones))
		{
			return static::$php_timezones;
		}
		
		$timezones = \DateTimeZone::listAbbreviations();
		foreach ($timezones as $area)
		{
			foreach ($area as $country)
			{
				static::$php_timezones[$country['timezone_id']] = array('offset' => $country['offset'], 'dst' => $country['dst']);

				$offset = (int) ($country['offset'] / 3600);
				if ( ! array_key_exists($offset, static::$offset_to_timezone) ||
					 ! array_key_exists((int) $country['dst'], static::$offset_to_timezone[$offset]))
				{
					static::$offset_to_timezone[$offset][$country['dst']] = $country['timezone_id'];
				}
			}
		}
		echo '<pre>';
		exit(print_r(\DateTimeZone::listAbbreviations()));

		return static::$php_timezones;
	}

	/**
	 * Takes an offset in seconds and returns its PHP named timezone
	 *
	 * @param int $offset
	 * @return string
	 */
	public static function offset_to_timezone($offset, $dst = false)
	{
		static::list_timezones();

		return static::$offset_to_timezone[(int) $offset][(int) $dst];
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
	 * @param	int|Date	time to display
	 * @param	string		either a named pattern from date config file or a pattern
	 * @return	string
	 */
	public function format($pattern_key = 'human')
	{
		Config::load('date', 'date');

		$pattern = Config::get('date.patterns.'.$pattern_key, null);
		$pattern = ($pattern === null) ? $pattern_key : $pattern;

		// Temporarily change timezone when different from default
		if (static::$default_timezone != $this->timezone)
		{
			date_default_timezone_set($this->timezone);
		}
		
		// Create output
		$output = strftime($pattern, $this->timestamp);

		// Change timezone back to default if changed previously
		if (static::$default_timezone != $this->timezone)
		{
			date_default_timezone_set(static::$default_timezone);
		}

		return $output;
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
		return $this->timezone;
	}

	/**
	 * Change the timezone
	 *
	 * @param	string|int	timezone as offset in hours or PHP named
	 * @param	bool		whether or not DST is active, only used for numeric input
	 */
	public function set_timezone($timezone, $dst = false)
	{
		if (is_string($timezone))
		{
			$this->timezone = $timezone;
		}
		else
		{
			$offset = (int) $timezone;
			$this->timezone = static::offset_to_timezone($offset, $dst);
		}
		
		return $this;
	}
	
	/**
	 * Allows you to just put the object in a string and get it inserted in the default pattern
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->format(static::$default_pattern);
	}
}

/* End of file date.php */