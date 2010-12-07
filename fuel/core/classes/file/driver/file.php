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

class File_Driver_File {

	/**
	 * @var	string	path to the file
	 */
	protected $path;

	/**
	 * @var	bool	whether this object locked the file
	 */
	protected $locked = false;

	/**
	 * @var	bool	whether the current object is read only
	 */
	protected $readonly = false;

	public function __construct($path, Array $config, File_Area $area)
	{

	}

	public function __destruct()
	{
		if ($this->locked)
		{
			flock($this->path, LOCK_UN);
		}
	}

	/**
	 * Create file
	 *
	 * @param	string	path where to create the file
	 * @param	string	filename of the new file
	 * @return	bool
	 */
	public static function _create($path, $filename)
	{

	}

	/**
	 * Read file
	 *
	 * @param	bool	whether to use file_get_contents() or readfile()
	 * @return	string|IO
	 */
	public static function _read($path, $as_string = false)
	{

	}

	public function read($as_string = false)
	{
		return static::_read($this->path, $as_string);
	}

	/**
	 * Rename file, only within current directory
	 *
	 * @param	string			new filename
	 * @param	string|false	new extension, false to keep current
	 * @return	bool
	 */
	public static function _rename($path, $new_path)
	{

	}

	public function rename($new_name, $new_extension = false)
	{
		// use static::_rename()
	}

	/**
	 * Move file to new directory
	 *
	 * @param	string	path to new directory, must be valid
	 * @return	bool
	 */
	public function move($new_path)
	{
		// use static::_rename()
	}

	/**
	 * Copy file
	 *
	 * @param	string	path to target directory, must be valid
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
	public function update($new_content)
	{

	}

	/**
	 * Delete file
	 *
	 * @return	bool
	 */
	public static function _delete($path)
	{

	}

	public function delete()
	{
		// use static::_delete()
	}
}

/* End of file file.php */