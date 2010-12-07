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

class File_Driver_Directory {

	/**
	 * @var	array	listing of files and directories within this directory
	 */
	protected $content = array();

	public function __construct($path, Array $config, File_Area $area)
	{

	}

	/**
	 * Create directory
	 *
	 * @param	string	path where to create the directory
	 * @param	string	name of the new directory
	 * @return	bool
	 */
	public static function _create($path, $filename)
	{

	}

	/**
	 * Read directory
	 *
	 * @param	whether or not to read recursive
	 * @return	array
	 */
	public static function _read($path, $depth = 0)
	{

	}

	public function read($depth = 0)
	{
		return static::_read($this->path, $depth);
	}

	/**
	 * Rename file, only within current directory
	 *
	 * @param	string	new directory name
	 * @return	bool
	 */
	public static function _rename($path, $new_path)
	{

	}

	public function rename($new_name)
	{
		// use static::_rename()
	}

	/**
	 * Move directory to new parent directory
	 *
	 * @param	string	path to new parent directory, must be valid
	 * @return	bool
	 */
	public function move($new_path)
	{
		// use static::_rename()
	}

	/**
	 * Copy directory
	 *
	 * @param	string	path to parent directory, must be valid
	 * @return	bool
	 */
	public static function _copy($path, $new_path)
	{

	}

	public function copy($new_path)
	{
		// use static::_copy()
	}

	/**
	 * Update contents
	 *
	 * @param	mixed	new file contents
	 * @return	bool
	 */
	public function update()
	{
		// don't know yet if this will do anything
	}

	/**
	 * Delete directory
	 *
	 * @return	bool
	 */
	public static function _delete($path, $recursive = false)
	{

	}

	public function delete($recursive = false)
	{
		// use static::_delete()
	}
}

/* End of file directory.php */