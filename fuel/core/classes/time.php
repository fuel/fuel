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
 * Time Class
 *
 * A renamed representation of the Date Helper from Kohana
 *
 * NOTE: This class has been taken from the Kohana framework and slightly modified,
 * but on the whole all credit goes to them. Over time this will be worked on.
 *
 * @package     Fuel
 * @category    Core
 * @author      Kohana Team
 * @modified    Alexandru Bucur
 * @copyright   (c) 2007-2011 Kohana Team
 * @license     http://kohanaframework.org/license
 * @link        http://fuelphp.com/docs/classes/time.html
 */

class Time {

	// Second amounts for various time increments
	const YEAR   = 31556926;
	const MONTH  = 2629744;
	const WEEK   = 604800;
	const DAY    = 86400;
	const HOUR   = 3600;
	const MINUTE = 60;

	// Available formats for Time::months()
	const MONTHS_LONG  = '%B';
	const MONTHS_SHORT = '%b';

	/**
	 * Default timestamp format for formatted_time
	 * @var  string
	 */
	public static $timestamp_format = 'Y-m-d H:i:s';

	/**
	 * Timezone for formatted_time
	 * @link http://uk2.php.net/manual/en/timezones.php
	 * @var  string
	 */
	public static $timezone;

	/**
	 * Returns the offset (in seconds) between two time zones. Use this to
	 * display dates to users in different time zones.
	 *
	 *     $seconds = Time::offset('America/Chicago', 'GMT');
	 *
	 * [!!] A list of time zones that PHP supports can be found at
	 * <http://php.net/timezones>.
	 *
	 * @param   string   timezone that to find the offset of
	 * @param   string   timezone used as the baseline
	 * @param   mixed    UNIX timestamp or date string
	 * @return  integer
	 */
	public static function offset($remote, $local = NULL, $now = NULL)
	{
		if ($local === NULL)
		{
			// Use the default timezone
			$local = date_default_timezone_get();
		}

		if (is_int($now))
		{
			// Convert the timestamp into a string
			$now = date(\DateTime::RFC2822, $now);
		}

		// Create timezone objects
		$zone_remote = new \DateTimeZone($remote);
		$zone_local  = new \DateTimeZone($local);

		// Create date objects from timezones
		$time_remote = new \DateTime($now, $zone_remote);
		$time_local  = new \DateTime($now, $zone_local);

		// Find the offset
		$offset = $zone_remote->getOffset($time_remote) - $zone_local->getOffset($time_local);

		return $offset;
	}

	/**
	 * Number of seconds in a minute, incrementing by a step. Typically used as
	 * a shortcut for generating a list that can used in a form.
	 *
	 *     $seconds = Time::seconds(); // 01, 02, 03, ..., 58, 59, 60
	 *
	 * @param   integer  amount to increment each step by, 1 to 30
	 * @param   integer  start value
	 * @param   integer  end value
	 * @return  array    A mirrored (foo => foo) array from 1-60.
	 */
	public static function seconds($step = 1, $start = 0, $end = 60)
	{
		// Always integer
		$step = (int) $step;

		$seconds = array();

		for ($i = $start; $i < $end; $i += $step)
		{
			$seconds[$i] = sprintf('%02d', $i);
		}

		return $seconds;
	}

	/**
	 * Number of minutes in an hour, incrementing by a step. Typically used as
	 * a shortcut for generating a list that can be used in a form.
	 *
	 *     $minutes = Time::minutes(); // 05, 10, 15, ..., 50, 55, 60
	 *
	 * @uses    Time::seconds
	 * @param   integer  amount to increment each step by, 1 to 30
	 * @return  array    A mirrored (foo => foo) array from 1-60.
	 */
	public static function minutes($step = 5)
	{
		// Because there are the same number of minutes as seconds in this set,
		// we choose to re-use seconds(), rather than creating an entirely new
		// function. Shhhh, it's cheating! ;) There are several more of these
		// in the following methods.
		return static::seconds($step);
	}

	/**
	 * Number of hours in a day. Typically used as a shortcut for generating a
	 * list that can be used in a form.
	 *
	 *     $hours = Time::hours(); // 01, 02, 03, ..., 10, 11, 12
	 *
	 * @param   integer  amount to increment each step by
	 * @param   boolean  use 24-hour time
	 * @param   integer  the hour to start at
	 * @return  array    A mirrored (foo => foo) array from start-12 or start-23.
	 */
	public static function hours($step = 1, $long = FALSE, $start = NULL)
	{
		// Default values
		$step = (int) $step;
		$long = (bool) $long;
		$hours = array();

		// Set the default start if none was specified.
		if ($start === NULL)
		{
			$start = ($long === FALSE) ? 1 : 0;
		}

		$hours = array();

		// 24-hour time has 24 hours, instead of 12
		$size = ($long === TRUE) ? 23 : 12;

		for ($i = $start; $i <= $size; $i += $step)
		{
			$hours[$i] = (string) $i;
		}

		return $hours;
	}

	/**
	 * Returns AM or PM, based on a given hour (in 24 hour format).
	 *
	 *     $type = Time::ampm(12); // PM
	 *     $type = Time::ampm(1);  // AM
	 *
	 * @param   integer  number of the hour
	 * @return  string
	 */
	public static function ampm($hour)
	{
		// Always integer
		$hour = (int) $hour;

		return ($hour > 11) ? 'PM' : 'AM';
	}

	/**
	 * Adjusts a non-24-hour number into a 24-hour number.
	 *
	 *     $hour = Time::adjust(3, 'pm'); // 15
	 *
	 * @param   integer  hour to adjust
	 * @param   string   AM or PM
	 * @return  string
	 */
	public static function adjust($hour, $ampm)
	{
		$hour = (int) $hour;
		$ampm = strtolower($ampm);

		switch ($ampm)
		{
			case 'am':
				if ($hour == 12)
				{
					$hour = 0;
				}
			break;
			case 'pm':
				if ($hour < 12)
				{
					$hour += 12;
				}
			break;
		}

		return sprintf('%02d', $hour);
	}

	/**
	 * Number of days in a given month and year. Typically used as a shortcut
	 * for generating a list that can be used in a form.
	 *
	 *     $days = Time::days(4, 2010); // 1, 2, 3, ..., 28, 29, 30
	 *
	 * @param   integer  number of month
	 * @param   integer  number of year to check month, defaults to the current year
	 * @return  array    A mirrored (foo => foo) array of the days.
	 */
	public static function days($month, $year = NULL)
	{
		static $months;

		if ($year === NULL)
		{
			// Use the current year by default
			$year = date('Y');
		}

		// Always integers
		$month = (int) $month;
		$year  = (int) $year;

		// We use caching for months, because time functions are used
		if (empty($months[$year][$month]))
		{
			$months[$year][$month] = array();

			// Use date to find the number of days in the given month
			$total = date('t', mktime(1, 0, 0, $month, 1, $year)) + 1;

			for ($i = 1; $i < $total; $i++)
			{
				$months[$year][$month][$i] = (string) $i;
			}
		}

		return $months[$year][$month];
	}

	/**
	 * Number of months in a year. Typically used as a shortcut for generating
	 * a list that can be used in a form.
	 *
	 * By default a mirrored array of $month_number => $month_number is returned
	 *
	 *     $months = Time::months();
	 *     // aray(1 => 1, 2 => 2, 3 => 3, ..., 12 => 12)
	 *
	 * But you can customise this by passing in either Time::MONTHS_LONG
	 *
	 *     $months = Time::months(Time::MONTHS_LONG);
	 *     // array(1 => 'January', 2 => 'February', ..., 12 => 'December')
	 *
	 * Or Time::MONTHS_SHORT
	 *
	 *     $months = Time::months(Time::MONTHS_SHORT);
	 *     // array(1 => 'Jan', 2 => 'Feb', ..., 12 => 'Dec')
	 *
	 * @uses    Time::hours
	 * @param   string The format to use for months
	 * @return  array  An array of months based on the specified format
	 */
	public static function months($format = NULL)
	{
		$months = array();

		if ($format === static::MONTHS_LONG OR $format === static::MONTHS_SHORT)
		{
			for ($i = 1; $i <= 12; ++$i)
			{
				$months[$i] = strftime($format, mktime(0, 0, 0, $i, 1));
			}
		}
		else
		{
			$months = static::hours();
		}

		return $months;
	}

	/**
	 * Returns an array of years between a starting and ending year. By default,
	 * the the current year - 5 and current year + 5 will be used. Typically used
	 * as a shortcut for generating a list that can be used in a form.
	 *
	 *     $years = Time::years(2000, 2010); // 2000, 2001, ..., 2009, 2010
	 *
	 * @param   integer  starting year (default is current year - 5)
	 * @param   integer  ending year (default is current year + 5)
	 * @return  array
	 */
	public static function years($start = NULL, $end = NULL)
	{
		// Default values
		$start = ($start === NULL) ? (date('Y') - 5) : (int) $start;
		$end   = ($end   === NULL) ? (date('Y') + 5) : (int) $end;

		$years = array();

		for ($i = $start; $i <= $end; $i++)
		{
			$years[$i] = (string) $i;
		}

		return $years;
	}

	/**
	 * Returns time difference between two timestamps, in human readable format.
	 * If the second timestamp is not given, the current time will be used.
	 * Also consider using [Time::fuzzy_span] when displaying a span.
	 *
	 *     $span = Time::span(60, 182, array('minutes','seconds')); // array('minutes' => 2, 'seconds' => 2)
	 *     $span = Time::span(60, 182, array('minutes')); // 2
	 *
	 * @param   integer  timestamp to find the span of
	 * @param   integer  timestamp to use as the baseline
	 * @param   array    formatting array
	 * @return  string   when only a single output is requested
	 * @return  array    associative list of all outputs requested
	 */
	public static function span($remote, $local = NULL, $output = NULL)
	{
		if ($output === NULL)
		{
			$output = array('years', 'months', 'weeks', 'days', 'hours', 'minutes', 'seconds');
		}
		else if (is_array($output) === FALSE)
		{
			return FALSE;
		}

		// Make the output values into keys
		$output = array_flip($output);

		if ($local === NULL)
		{
			// Calculate the span from the current time
			$local = time();
		}

		// Calculate timespan (seconds)
		$timespan = abs($remote - $local);

		if (isset($output['years']))
		{
			$timespan -= static::YEAR * ($output['years'] = (int) floor($timespan / static::YEAR));
		}

		if (isset($output['months']))
		{
			$timespan -= static::MONTH * ($output['months'] = (int) floor($timespan / static::MONTH));
		}

		if (isset($output['weeks']))
		{
			$timespan -= static::WEEK * ($output['weeks'] = (int) floor($timespan / static::WEEK));
		}

		if (isset($output['days']))
		{
			$timespan -= static::DAY * ($output['days'] = (int) floor($timespan / static::DAY));
		}

		if (isset($output['hours']))
		{
			$timespan -= static::HOUR * ($output['hours'] = (int) floor($timespan / static::HOUR));
		}

		if (isset($output['minutes']))
		{
			$timespan -= static::MINUTE * ($output['minutes'] = (int) floor($timespan / static::MINUTE));
		}

		// Seconds ago, 1
		if (isset($output['seconds']))
		{
			$output['seconds'] = $timespan;
		}

		if (count($output) === 1)
		{
			// Only a single output was requested, return it
			return array_pop($output);
		}

		// Return array
		return $output;
	}

	/**
	 * Returns the difference between a time and now in a "fuzzy" way.
	 * Displaying a fuzzy time instead of a date is usually faster to read and understand.
	 *
	 *     $span = Time::fuzzy_span(time() - 10); // "moments ago"
	 *     $span = Time::fuzzy_span(time() + 20); // "in moments"
	 *
	 * A second parameter is available to manually set the "local" timestamp,
	 * however this parameter shouldn't be needed in normal usage and is only
	 * included for unit tests
	 *
	 * @param   integer  "remote" timestamp
	 * @param   integer  "local" timestamp, defaults to time()
	 * @return  string
	 */
	public static function fuzzy_span($timestamp, $local_timestamp = NULL)
	{
		$local_timestamp = ($local_timestamp === NULL) ? time() : (int) $local_timestamp;

		// Determine the difference in seconds
		$offset = abs($local_timestamp - $timestamp);

		if ($offset <= static::MINUTE)
		{
			$span = 'moments';
		}
		elseif ($offset < (static::MINUTE * 20))
		{
			$span = 'a few minutes';
		}
		elseif ($offset < static::HOUR)
		{
			$span = 'less than an hour';
		}
		elseif ($offset < (static::HOUR * 4))
		{
			$span = 'a couple of hours';
		}
		elseif ($offset < static::DAY)
		{
			$span = 'less than a day';
		}
		elseif ($offset < (static::DAY * 2))
		{
			$span = 'about a day';
		}
		elseif ($offset < (static::DAY * 4))
		{
			$span = 'a couple of days';
		}
		elseif ($offset < static::WEEK)
		{
			$span = 'less than a week';
		}
		elseif ($offset < (static::WEEK * 2))
		{
			$span = 'about a week';
		}
		elseif ($offset < static::MONTH)
		{
			$span = 'less than a month';
		}
		elseif ($offset < (static::MONTH * 2))
		{
			$span = 'about a month';
		}
		elseif ($offset < (static::MONTH * 4))
		{
			$span = 'a couple of months';
		}
		elseif ($offset < static::YEAR)
		{
			$span = 'less than a year';
		}
		elseif ($offset < (static::YEAR * 2))
		{
			$span = 'about a year';
		}
		elseif ($offset < (static::YEAR * 4))
		{
			$span = 'a couple of years';
		}
		elseif ($offset < (static::YEAR * 8))
		{
			$span = 'a few years';
		}
		elseif ($offset < (static::YEAR * 12))
		{
			$span = 'about a decade';
		}
		elseif ($offset < (static::YEAR * 24))
		{
			$span = 'a couple of decades';
		}
		elseif ($offset < (static::YEAR * 64))
		{
			$span = 'several decades';
		}
		else
		{
			$span = 'a long time';
		}

		if ($timestamp <= $local_timestamp)
		{
			// This is in the past
			return $span.' ago';
		}
		else
		{
			// This in the future
			return 'in '.$span;
		}
	}

	/**
	 * Converts a UNIX timestamp to DOS format. There are very few cases where
	 * this is needed, but some binary formats use it (eg: zip files.)
	 * Converting the other direction is done using {@link Time::dos2unix}.
	 *
	 *     $dos = Time::unix2dos($unix);
	 *
	 * @param   integer  UNIX timestamp
	 * @return  integer
	 */
	public static function unix2dos($timestamp = NULL)
	{
		$timestamp = ($timestamp === NULL) ? getdate() : getdate($timestamp);

		if ($timestamp['year'] < 1980)
		{
			return (1 << 21 | 1 << 16);
		}

		$timestamp['year'] -= 1980;

		// What voodoo is this? I have no idea... Geert can explain it though,
		// and that's good enough for me.
		return ($timestamp['year']    << 25 | $timestamp['mon']     << 21 |
		        $timestamp['mday']    << 16 | $timestamp['hours']   << 11 |
		        $timestamp['minutes'] << 5  | $timestamp['seconds'] >> 1);
	}

	/**
	 * Converts a DOS timestamp to UNIX format.There are very few cases where
	 * this is needed, but some binary formats use it (eg: zip files.)
	 * Converting the other direction is done using {@link Time::unix2dos}.
	 *
	 *     $unix = Time::dos2unix($dos);
	 *
	 * @param   integer  DOS timestamp
	 * @return  integer
	 */
	public static function dos2unix($timestamp = NULL)
	{
		$sec  = 2 * ($timestamp & 0x1f);
		$min  = ($timestamp >>  5) & 0x3f;
		$hrs  = ($timestamp >> 11) & 0x1f;
		$day  = ($timestamp >> 16) & 0x1f;
		$mon  = ($timestamp >> 21) & 0x0f;
		$year = ($timestamp >> 25) & 0x7f;

		return mktime($hrs, $min, $sec, $mon, $day, $year + 1980);
	}

	/**
	 * Returns a date/time string with the specified timestamp format
	 *
	 *     $time = Time::formatted_time('5 minutes ago');
	 *
	 * @see     http://php.net/manual/en/datetime.construct.php
	 * @param   string  datetime_str     datetime string
	 * @param   string  timestamp_format timestamp format
	 * @return  string
	 */
	public static function formatted_time($datetime_str = 'now', $timestamp_format = NULL, $timezone = NULL)
	{
		$timestamp_format = ($timestamp_format == NULL) ? static::$timestamp_format : $timestamp_format;
		$timezone         = ($timezone === NULL) ? static::$timezone : $timezone;

		$time = new \DateTime($datetime_str, new \DateTimeZone(
			$timezone ? $timezone : date_default_timezone_get()
		));

		return $time->format($timestamp_format);
	}

}

/* End of file time.php */