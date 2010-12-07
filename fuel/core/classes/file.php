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

	/**
	 * Create directory or empty file
	 *
	 * @param	string	directory where to create file or dir
	 * @param	string	file or directory name
	 * @return	bool
	 */
	public static function create($basepath, $name)
	{
		$info = pathinfo(rtrim($basepath, '/\\').DS.$name);

		if (empty($info['extension']))
		{
			return File_Driver_File::_create($info['dirname'], $info['basename']);
		}
		else
		{
			return File_Driver_Directory::_create($info['dirname'], $info['basename']);
		}
	}

	/**
	 * Read directory or file
	 *
	 * @param	string		file or directory to read
	 * @param	int|bool	depth to recurse directory, 1 is only current and 0 or smaller is unlimited
	 * @return	mixed		file contents or directory contents in an array
	 */
	public static function read($path, $depth = 0)
	{
		if (is_file($path))
		{
			return File_Driver_File::_read($path, $depth);
		}
		elseif (is_dir($path))
		{
			return File_Driver_Directory::_read($path, $depth);
		}

		Error::notice('Invalid file or directory path.');
		return false;
	}

	/**
	 * Rename directory or file
	 *
	 * @param	string	path to file or directory to rename
	 * @param	string	new path (full path, can also cause move)
	 * @return	bool
	 */
	public static function rename($path, $new_path)
	{
		if (is_file($path))
		{
			return File_Driver_File::_rename($path, $new_path);
		}
		elseif (is_dir($path))
		{
			return File_Driver_Directory::_rename($path, $new_path);
		}

		Error::notice('Invalid file or directory path.');
		return false;
	}

	/**
	 * Copy directory or file
	 *
	 * @param	string	path to file or directory to rename
	 * @param	string	new base directory (full path)
	 * @return	bool
	 */
	public static function copy($path, $new_path)
	{
		if (is_file($path))
		{
			return File_Driver_File::_copy($path, $new_path);
		}
		elseif (is_dir($path))
		{
			return File_Driver_Directory::_copy($path, $new_path);
		}

		Error::notice('Invalid file or directory path.');
		return false;
	}

	/**
	 * Rename directory or file
	 *
	 * @param	string	path to file or directory to delete
	 * @param	bool	whether to also delete contents of subdirectories in case of directory
	 * @return	bool
	 */
	public static function delete($path, $recursive = false)
	{
		if (is_file($path))
		{
			return File_Driver_File::_delete($path);
		}
		elseif (is_dir($path))
		{
			return File_Driver_Directory::_delete($path, $recursive);
		}

		Error::notice('Invalid file or directory path.');
		return false;
	}
}

/* End of file file.php */