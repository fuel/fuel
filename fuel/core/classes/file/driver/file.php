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

	public function create($path)
	{

	}

	public function read($path)
	{

	}

	public function update($path)
	{

	}

	public function delete($path)
	{

	}
}

/* End of file file.php */