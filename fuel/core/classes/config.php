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

class Config {

	public static $loaded_files = array();

	public static $items = array();

	public static function load($file, $group = null, $reload = false)
	{
		if ( ! is_array($file) && array_key_exists($file, static::$loaded_files) and ! $reload)
		{
			return false;
		}

		$config = array();
		if (is_array($file))
		{
			$config = $file;
		}
		elseif ($paths = \Fuel::find_file('config', $file, '.php', true))
		{
			// Reverse the file list so that we load the core configs first and
			// the app can override anything.
			$paths = array_reverse($paths);
			foreach ($paths as $path)
			{
				$config = \Fuel::load($path) + $config;
			}
		}
		if ($group === null)
		{
			static::$items = $reload ? $config : static::$items + $config;
		}
		else
		{
			$group = ($group === true) ? $file : $group;
			if ( ! isset(static::$items[$group]) or $reload)
			{
				static::$items[$group] = array();
			}
			static::$items[$group] = static::$items[$group] + $config;
		}

		if ( ! is_array($file))
		{
			static::$loaded_files[$file] = true;
		}
		return $config;
	}

	public static function save($file, $config)
	{
		if ( ! is_array($config))
		{
			if ( ! isset(static::$items[$config]))
			{
				return false;
			}
			$config = static::$items[$config];
		}
		$content = <<<CONF
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
 * @copyright	2011 Fuel Development Team
 * @link		http://fuelphp.com
 */


CONF;
		$content .= 'return '.str_replace('  ', "\t", var_export($config, true)).';';
		$content .= <<<CONF


/* End of file $file.php */
CONF;

		($path = \Fuel::find_file('config', $file, '.php')) or $path[0] = APPPATH.'config'.DS.$file.'.php';

		$path = pathinfo($path[0]);

		return File::update($path['dirname'], $path['basename'], $content);
	}

	public static function get($item, $default = null)
	{
		if (isset(static::$items[$item]))
		{
			return static::$items[$item];
		}

		if (strpos($item, '.') !== false)
		{
			$parts = explode('.', $item);

			switch (count($parts))
			{
				case 2:
					if (isset(static::$items[$parts[0]][$parts[1]]))
					{
						return static::$items[$parts[0]][$parts[1]];
					}
				break;

				case 3:
					if (isset(static::$items[$parts[0]][$parts[1]][$parts[2]]))
					{
						return static::$items[$parts[0]][$parts[1]][$parts[2]];
					}
				break;

				case 4:
					if (isset(static::$items[$parts[0]][$parts[1]][$parts[2]][$parts[3]]))
					{
						return static::$items[$parts[0]][$parts[1]][$parts[2]][$parts[3]];
					}
				break;

				default:
					$return = false;
					foreach ($parts as $part)
					{
						if ($return === false and isset(static::$items[$part]))
						{
							$return = static::$items[$part];
						}
						elseif (isset($return[$part]))
						{
							$return = $return[$part];
						}
						else
						{
							return $default;
						}
					}
					return $return;
				break;
			}
		}

		return $default;
	}

	public static function set($item, $value)
	{
		$parts = explode('.', $item);

		switch (count($parts))
		{
			case 1:
				static::$items[$parts[0]] = $value;
			break;

			case 2:
				static::$items[$parts[0]][$parts[1]] = $value;
			break;

			case 3:
				static::$items[$parts[0]][$parts[1]][$parts[2]] = $value;
			break;

			case 4:
				static::$items[$parts[0]][$parts[1]][$parts[2]][$parts[3]] = $value;
			break;

			default:
				$item =& static::$items;
				foreach ($parts as $part)
				{
					// if it's not an array it can't have a subvalue
					if ( ! is_array($item))
					{
						return false;
					}

					// if the part didn't exist yet: add it
					if ( ! isset($item[$part]))
					{
						$item[$part] = array();
					}

					$item =& $item[$part];
				}
				$item = $value;
			break;
		}
		return true;
	}
}

/* End of file config.php */
