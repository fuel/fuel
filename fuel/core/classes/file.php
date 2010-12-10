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
	 * Create an empty file
	 *
	 * @param	string	directory where to create file
	 * @param	string	filename
	 * @return	bool
	 */
	public static function create($basepath, $name)
	{

	}

	/**
	 * Create an empty directory
	 *
	 * @param	string	directory where to create new dir
	 * @param	string	dirname
	 * @return	bool
	 */
	public static function create_dir($basepath, $name)
	{

	}

	/**
	 * Read file
	 *
	 * @param	string		file to read
	 * @param	bool		whether to use readfile() or file_get_contents()
	 * @return	IO|string	file contents
	 */
	public static function read($path, $as_string = false)
	{
		// return either readfile() or file_get_contents()
	}

	/**
	 * Read directory
	 *
	 * @param	string	directory to read
	 * @param	int		depth to recurse directory, 1 is only current and 0 or smaller is unlimited
	 * @return	array	directory contents in an array
	 */
	public static function read_dir($path, $depth = 0)
	{

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
	 * Alias for rename(), not needed but consistent with other methods
	 */
	public static function rename_dir($path, $new_path)
	{
		return static::rename($path, $new_path);
	}

	/**
	 * Copy file
	 *
	 * @param	string	path to file to copy
	 * @param	string	new base directory (full path)
	 * @return	bool
	 */
	public static function copy($path, $new_path)
	{

	}

	/**
	 * Copy directory
	 *
	 * @param	string	path to directory to copy
	 * @param	string	new base directory (full path)
	 * @return	bool
	 */
	public static function copy_dir($path, $new_path)
	{

	}

	/**
	 * Delete file
	 *
	 * @param	string	path to file to delete
	 * @return	bool
	 */
	public static function delete($path)
	{

	}

	/**
	 * Delete directory
	 *
	 * @param	string	path to directory to delete
	 * @param	bool	whether to also delete contents of subdirectories
	 * @return	bool
	 */
	public static function delete_dir($path, $recursive = false)
	{

	}
}

/* End of file file.php */