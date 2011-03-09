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



class File_Driver_Directory {

	/**
	 * @var	array	listing of files and directories within this directory
	 */
	protected $content = array();

	protected function __construct($path, Array &$config, File_Area $area, $content = array())
	{
		$this->path		= rtrim($path, '\\/').DS;
		$this->resource = false;

		foreach ($content as $key => $value)
		{
			if ( ! is_int($key))
			{
				$this->content[$key] = $value === false ? false : $area->get_driver($path.DS.$key, $config, $value);
			}
			else
			{
				$this->content[$key] = $area->get_driver($path.DS.$value, $config);
			}
		}
	}

	public static function factory($path, Array $config = array(), File_Area $area = null, $content = array())
	{
		return new static($path, $config, $area, $content);
	}

	/**
	 * Read directory
	 *
	 * @param	whether or not to read recursive
	 * @return	array
	 */
	public function read($depth = 0)
	{
		return $this->area->read_dir($this->path, $depth, null, $this->area);
	}

	/**
	 * Rename file, only within current directory
	 *
	 * @param	string	new directory name
	 * @return	bool
	 */
	public function rename($new_name)
	{
		$info = pathinfo($this->path);

		$new_name = str_replace(array('..', '/', '\\'), array('', '', ''), $new_name);

		$new_path = $info['dirname'].DS.$new_name;

		return $this->area->rename_dir($this->path, $new_path);
	}

	/**
	 * Move directory to new parent directory
	 *
	 * @param	string	path to new parent directory, must be valid
	 * @return	bool
	 */
	public function move($new_path)
	{
		$info = pathinfo($this->path);
		$new_path = $this->area->get_path($new_path);

		$new_path = rtrim($new_path, '\\/').DS.$info['basename'];

		return $this->area->rename_dir($this->path, $new_path);
	}

	/**
	 * Copy directory
	 *
	 * @param	string	path to parent directory, must be valid
	 * @return	bool
	 */
	public function copy($new_path)
	{
		$info = pathinfo($this->path);
		$new_path = $this->area->get_path($new_path);

		$new_path = rtrim($new_path, '\\/').DS.$info['basename'];

		return $this->area->copy_dir($this->path, $new_path);
	}

	/**
	 * Update contents
	 *
	 * @param	mixed	new file contents
	 * @return	bool
	 */
	public function update()
	{
		throw new \File_Exception('Update method is unavailable on directories.');
	}

	/**
	 * Delete directory
	 *
	 * @return	bool
	 */
	public function delete($recursive = true, $delete_top = true)
	{
		// should also destroy object but not possible in PHP right?
		return $this->area->delete_dir($this->path, $recursive, $delete_top);
	}
}

/* End of file directory.php */
