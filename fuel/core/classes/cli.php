<?php

namespace Fuel;

class Cli {

	protected static $args = array();

	/**
	 * Returns the option with the given name.  You can also give the option
	 * number.
	 *
	 * Named options must be in the following formats:
	 * php index.php user -v --v -name=John --name=John
	 *
	 * @param	string|int	$name	the name of the option (int if unnamed)
	 * @return	string
	 */
	public static function option($name)
	{
		if ( ! isset(static::$args[$name]))
		{
			return null;
		}
		return static::$args[$name];
	}

	/**
	 * Static constructor.  Parses all the CLI params.
	 */
	public static function _init()
	{
		for ($i = 1; $i < $_SERVER['argc']; $i++)
		{
			$arg = explode('=', $_SERVER['argv'][$i]);

			static::$args[$i] = $arg[0];

			if (count($arg) > 1 || strncmp($arg[0], '-', 1) === 0)
			{
				static::$args[ltrim($arg[0], '-')] = isset($arg[1]) ? $arg[1] : true;
			}
		}
	}

}