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

namespace Fuel\Core;

use Fuel\App as App;

class File_Driver_File {

	/**
	 * @var	string	path to the file
	 */
	protected $path;

	/**
	 * @var	File_Area
	 */
	protected $area;

	/**
	 * @var	Resource	file resource
	 */
	protected $resource;

	/**
	 * @var	bool	whether the current object is read only
	 */
	protected $readonly = false;

	protected function __construct($path, Array $config, File_Area $area) {}

	public static function factory($path, Array $config = array(), File_Area $area = null)
	{
		$obj = new static($path, $config, App\File::instance($area));

		$config['path'] = $path;
		$config['area'] = $area;
		foreach ($config as $key => $value)
		{
			if (property_exists($obj, $key) && empty($obj->$key))
			{
				$obj->$key = $value;
			}
		}

		if (is_null($obj->resource))
		{
			$obj->resource = App\File::open_file($obj->path, true, $obj->area);
		}
	}

	public function __destruct()
	{
		if (is_resource($this->resource))
		{
			App\File::close_file($this->resource, $this->area);
		}
	}

	/**
	 * Read file
	 *
	 * @param	bool	whether to use file_get_contents() or readfile()
	 * @return	string|IO
	 */
	public function read($as_string = false)
	{
		return $this->area->read($this->path, $as_string);
	}

	/**
	 * Rename file, only within current directory
	 *
	 * @param	string			new filename
	 * @param	string|false	new extension, false to keep current
	 * @return	bool
	 */
	public function rename($new_name, $new_extension = false)
	{
		$info = pathinfo($this->path);

		$new_name = str_replace(array('..', '/', '\\'), array('', '', ''), $new_name);
		$extension = $new_extension === false
			? $info['extension']
			: ltrim(str_replace(array('/', '\\'), array('', '', ''), $new_name), '.');
		$extension = ! empty($extension) ? '.'.$extension : '';

		$new_path = $info['dirname'].DS.$new_name.$extension;

		return $this->area->rename($this->path, $new_path);
	}

	/**
	 * Move file to new directory
	 *
	 * @param	string	path to new directory, must be valid
	 * @return	bool
	 */
	public function move($new_path)
	{
		$info = pathinfo($this->path);
		$new_path = $this->area->get_path($new_path);

		$new_path = rtrim($new_path, '\\/').DS.$info['basename'];

		return $this->area->rename($this->path, $new_path);
	}

	/**
	 * Copy file
	 *
	 * @param	string	path to target directory, must be valid
	 * @return	bool
	 */
	public function copy($new_path)
	{
		$info = pathinfo($this->path);
		$new_path = $this->area->get_path($new_path);

		$new_path = rtrim($new_path, '\\/').DS.$info['basename'];

		return $this->area->copy($this->path, $new_path);
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
	public function delete()
	{
		// should also destroy object but not possible in PHP right?
		return $this->area->delete($this->path);
	}
}

/* End of file file.php */