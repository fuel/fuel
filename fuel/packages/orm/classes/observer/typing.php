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

class Observer_Typing extends Observer {

	public static $type_methods = array(
		'/^varchar\\[[0-9]+\\]$/uiD'
			=> 'Orm\\Observer::type_varchar',
		'/^int(eger)?$/uiD'
			=> 'Orm\\Observer::type_integer',
		'/^(float|double)?$/uiD'
			=> 'Orm\\Observer::type_integer',
		'/^text$/'
			=> false,
		'/^serialize$/'
			=> array(
				'before_save' => 'Orm\\Observer::type_serialize',
				'after_load'  => 'Orm\\Observer::type_unserialize',
			),
		'/^json$/'
			=> array(
				'before_save' => 'Orm\\Observer::type_json_encode',
				'after_load'  => 'Orm\\Observer::type_json_decode',
			),
	);

	public function before_save(Model $obj)
	{
		$properties = $obj->properties();

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
					$method = ! empty($method[__FUNCTION__]) ? $method[__FUNCTION__] : false;
				}

				if ($method and preg_match($match, $settings['type']) > 0)
				{
					$obj->{$p} = call_user_func($method, $obj->{$p}, $settings['type']);
				}
			}
		}
	}

	public function after_load(Model $obj)
	{
		$properties = $obj->properties();

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
					$method = ! empty($method[__FUNCTION__]) ? $method[__FUNCTION__] : false;
				}

				if ($method and preg_match($match, $settings['type']) > 0)
				{
					$obj->{$p} = call_user_func($method, $obj->{$p}, $settings['type']);
				}
			}
		}
	}

	public static function type_varchar($var, $type)
	{
		$var = strval($var);
		$length = intval(substr($type, 8, -1));
		strlen($var) > $length and $var = substr($var, 0, $length);

		return $var;
	}

	public static function type_integer($var)
	{
		return intval($var);
	}

	public static function type_float($var)
	{
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

// End of file typing.php