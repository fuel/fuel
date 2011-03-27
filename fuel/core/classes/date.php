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
 * Date Class
 *
 * DateTime replacement that supports internationalization and does correction to GMT
 * when your webserver isn't configured correctly.
 *
 * @package		Fuel
 * @subpackage	Core
 * @category	Core
 * @author		Jelmer Schreuder
 * @link		http://fuelphp.com/docs/classes/date.html
 *
 * Notes:
 * - Always returns Date objects, will accept both Date objects and UNIX timestamps
 * - create_time() uses strptime and has currently a very bad hack to use strtotime for windows servers
 * - Uses strftime formatting for dates www.php.net/manual/en/function.strftime.php
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
	 * @var string default timezone, must be valid PHP timezone from www.php.net/timezones
	 */
	protected static $default_timezone;

	/**
	 * @var string default pattern for date output
	 */
	protected static $default_pattern = 'local';

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
		static::$server_gmt_offset	= \Config::get('server_gmt_offset', 0);

		// Set the default timezone
		static::$default_timezone	= date_default_timezone_get();

		// Ugly temporary windows fix because windows doesn't support strptime()
		// Better fix will accept custom pattern parsing but only parse numeric input on windows servers
		if ( ! function_exists('strptime') && ! function_exists('Fuel\Core\strptime'))
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
				// output this awful array instead of a timestamp LIKE EVERYONE ELSE DOES!!!
			}
		}
	}

	/**
	 * Create Date object from timestamp, timezone is optional
	 *
	 * @param	int		UNIX timestamp from current server
	 * @param	string	valid PHP timezone from www.php.net/timezones
	 * @return	Date
	 */
	public static function factory($timestamp = null, $timezone = null)
	{
		$timestamp	= is_null($timestamp) ? time() + static::$server_gmt_offset : $timestamp;
		$timezone	= is_null($timezone) ? static::$default_timezone : $timezone;

		return new static($timestamp, $timezone);
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
	public static function create_from_string($input, $pattern_key = 'local')
	{
		\Config::load('date', 'date');

		$pattern = \Config::get('date.patterns.'.$pattern_key, null);
		$pattern = ($pattern === null) ? $pattern_key : $pattern;

		$time = strptime($input, $pattern);
		if ($time === false)
		{
			\Error::notice('Input was not recognized by pattern.');
			return false;
		}
		$timestamp = mktime($time['tm_hour'], $time['tm_min'], $time['tm_sec'],
						$time['tm_mon'], $time['tm_mday'], $time['tm_year']+1901 );

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
		$interval	= (is_int($interval)) ? $interval : strtotime($interval, $start->get_timestamp()) - $start->get_timestamp();

		if ($interval <= 0)
		{
			\Error::notice('Input was not recognized by pattern.');
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
			\Error::notice('Invalid input for month given.');
			return false;
		}
		elseif ($month == 2)
		{
			if ($year % 400 == 0 || ($year % 4 == 0 && $year % 100 != 0))
			{
				return 29;
			}
		}

		$days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
		return $days_in_month[$month-1];
	}
	
	/**
	 * Returns the time ago
	 *
	 * @param	int		UNIX timestamp from current server
	 * @return	string	Time ago
	 */
	public static function time_ago($timestamp)
	{
        if ( $timestamp === null )
		{
			return;
		}
		
		\Lang::load('date', true);
		
		$difference = time() - $timestamp;
		$periods	= array('second', 'minute', 'hour', 'day', 'week', 'month', 'years', 'decade');
 		$lengths	= array(60, 60, 24, 7, 4.35, 12, 10);

		for ($j = 0; $difference >= $lengths[$j]; $j++)
		{
        	$difference /= $lengths[$j];
		}

        $difference = round($difference);

		if ( $difference != 1 )
		{
			$periods[$j] = \Inflector::pluralize($periods[$j]);
		}
		
		$text = \Lang::line('date.text', array(
			'time' => \Lang::line('date.'.$periods[$j], array('t' => $difference))
		));
		
		return $text;
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
	 * @param	string	either a named pattern from date config file or a pattern
	 * @return	string
	 */
	public function format($pattern_key = 'local')
	{
		\Config::load('date', 'date');

		$pattern = \Config::get('date.patterns.'.$pattern_key, $pattern_key);

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
	 * @return	string
	 */
	public function get_timestamp()
	{
		return $this->timestamp;
	}

	/**
	 * Returns the internal timezone
	 *
	 * @return	string
	 */
	public function get_timezone()
	{
		return $this->timezone;
	}

	/**
	 * Change the timezone
	 *
	 * @param	string	timezone from www.php.net/timezones
	 */
	public function set_timezone($timezone)
	{
		$this->timezone = $timezone;

		return $this;
	}

	/**
	 * Allows you to just put the object in a string and get it inserted in the default pattern
	 *
	 * @return	string
	 */
	public function __toString()
	{
		return $this->format(static::$default_pattern);
	}
}

/* End of file date.php */
