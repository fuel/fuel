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

namespace Orm;

class Observer_Typing {

	/**
	 * @var  array  types of events to act on and whether they are pre- or post-database
	 */
	public static $events = array(
		'before_save'  => 'before',
		'after_save'   => 'after',
		'after_load'   => 'after',
	);

	/**
	 * @var  array  regexes for db types with the method(s) to use, optionally pre- or post-database
	 */
	public static $type_methods = array(
		'/^varchar/uiD' => array(
			'before' => 'Orm\\Observer_Typing::type_varchar'
			),
		'/^(tiny|small|medium|big)?int(eger)?/uiD'
			=> 'Orm\\Observer_Typing::type_integer',
		'/^(float|double|decimal)/uiD'
			=> 'Orm\\Observer_Typing::type_float',
		'/^(tiny|medium|long)?text/' => array(
			'before' => 'Orm\\Observer_Typing::type_text'
		),
		'/^set\\(/uiD' => array(
			'before' => 'Orm\\Observer_Typing::type_set'
		),
		'/^enum\\(/uiD' => array(
			'before' => 'Orm\\Observer_Typing::type_set'
		),
		'/^serialize$/uiD' => array(
			'before' => 'Orm\\Observer_Typing::type_serialize',
			'after'  => 'Orm\\Observer_Typing::type_unserialize',
		),
		'/^json$/uiD' => array(
			'before' => 'Orm\\Observer_Typing::type_json_encode',
			'after'  => 'Orm\\Observer_Typing::type_json_decode',
		),
	);

	/**
	 * Get notified of an event
	 *
	 * @param  Model   $instance
	 * @param  string  $event
	 */
	public static function orm_notify(Model $instance, $event)
	{
		if ( ! array_key_exists($event, static::$events))
		{
			return;
		}

		$event_type = static::$events[$event];
		$properties = $instance->properties();

		foreach ($properties as $p => $settings)
		{
			if (empty($settings['type']))
			{
				continue;
			}

			foreach (static::$type_methods as $match => $method)
			{
				if (is_array($method))
				{
					$method = ! empty($method[$event_type]) ? $method[$event_type] : false;
				}

				if ($method and preg_match($match, $settings['type']) > 0)
				{
					$instance->{$p} = call_user_func($method, $instance->{$p}, $settings['type']);
				}
			}
		}
	}

	public static function type_varchar($var, $type)
	{
		if (is_array($var) or (is_object($var) and ! method_exists($var, '__toString')))
		{
			throw new InvalidContentType('Array or object could not be converted to varchar.');
		}

		$var = strval($var);
		$length = intval(substr($type, 8, -1));
		strlen($var) > $length and $var = substr($var, 0, $length);

		return $var;
	}

	public static function type_text($var, $type)
	{
		if (is_array($var) or (is_object($var) and ! method_exists($var, '__toString')))
		{
			throw new InvalidContentType('Array or object could not be converted to text.');
		}

		return strval($var);
	}

	public static function type_integer($var, $type)
	{
		if (is_array($var) or is_object($var))
		{
			throw new InvalidContentType('Array or object could not be converted to integer.');
		}

		if (strtolower(substr($type, 0, strlen('tinyint'))) == 'tinyint')
		{
			if ($var < -32768 or $var > 32767)
			{
				throw new InvalidContentType('Integer value outside of range.');
			}
		}
		elseif (strtolower(substr($type, 0, strlen('smallint'))) == 'smallint')
		{
			if ($var < -8388608 or $var > 8388607)
			{
				throw new InvalidContentType('Integer value outside of range.');
			}
		}
		elseif (strtolower(substr($type, 0, strlen('bigint'))) == 'bigint')
		{
			if ($var < intval('-9223372036854775808') or $var > intval('9223372036854775807'))
			{
				throw new InvalidContentType('Integer value outside of range.');
			}
		}
		else // assume int/integer
		{
			if ($var < intval('-2147483648') or $var > intval('2147483647'))
			{
				throw new InvalidContentType('Integer value outside of range.');
			}
		}

		return intval($var);
	}

	public static function type_float($var)
	{
		if (is_array($var) or is_object($var))
		{
			throw new InvalidContentType('Array or object could not be converted to float.');
		}

		return floatval($var);
	}

	public static function type_serialize($var)
	{
		return serialize($var);
	}

	public static function type_unserialize($var)
	{
		return unserialize($var);
	}

	public static function type_json_encode($var)
	{
		return json_encode($var);
	}

	public static function type_json_decode($var)
	{
		return json_decode($var);
	}
}

// Invalid content exception, thrown when conversion is not possible
class InvalidContentType extends Exception {}

// End of file typing.php