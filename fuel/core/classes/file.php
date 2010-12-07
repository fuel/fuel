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
 * File Class
 *
 * @package		Fuel
 * @subpackage	Core
 * @category	Core
 * @author		Jelmer Schreuder
 */
class File {

	/**
	 * @var	File_Area	points to the base area
	 */
	protected $base_area = null;

	/**
	 * @var	array	loaded area's
	 */
	protected $areas = array();

	public static function _init()
	{
		static::$base_area = File_Area::factory(Config::get('file.base_config', array()));

		foreach (Config::get('file.areas', array()) as $name => $config)
		{
			static::$areas[$name] = File_Area::factory($config);
		}
	}

	/**
	 * Factory
	 *
	 * @param	string					path to the file or directory
	 * @param	string|File_Area|null	file area name, object or null for non-specific
	 * @return	File_Driver_File
	 */
	public static function factory($path, $area = null, Array $config = array())
	{
		return static::instance($area)->get_driver($path, $config);
	}

	/**
	 * Instance
	 *
	 * @param	string|File_Area|null	file area name, object or null for non-specific
	 * @return	File_Area
	 */
	public static function instance($area = null)
	{
		return (is_string($area) ? static::$areas[$area] : $area) ?: static::$base_area;
	}

	public static function create($path, $input)
	{
		return static::instance()->create($path, $input);
	}

	public static function read($path)
	{
		return static::instance()->read($path);
	}

	public static function update($path, $new_content)
	{
		return static::instance()->update($path, $new_content);
	}

	public static function delete($path)
	{
		return static::instance()->delete($path);
	}
}

/* End of file file.php */