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

	public function __destruct() {}

	/**
	 * Read directory
	 *
	 * @param	whether or not to read recursive
	 * @return	array
	 */
	public function read($depth = 0)
	{
		return App\File::read_dir($this->path, $depth);
	}

	/**
	 * Rename file, only within current directory
	 *
	 * @param	string	new directory name
	 * @return	bool
	 */
	public function rename($new_name)
	{
		// use App\File::rename()
	}

	/**
	 * Move directory to new parent directory
	 *
	 * @param	string	path to new parent directory, must be valid
	 * @return	bool
	 */
	public function move($new_path)
	{
		// use App\File::rename()
	}

	/**
	 * Copy directory
	 *
	 * @param	string	path to parent directory, must be valid
	 * @return	bool
	 */
	public function copy($new_path)
	{
		// use App\File::copy_dir()
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
	public function delete($recursive = false)
	{
		// use App\File::delete_dir()
	}
}

/* End of file directory.php */