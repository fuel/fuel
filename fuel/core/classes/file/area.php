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

class File_Area {

	/**
	 * @var	string	path to basedir restriction, null for no restriction
	 */
	protected $basedir = null;

	/**
	 * @var	array	array of allowed extensions, null for all
	 */
	protected $extensions = null;

	/**
	 * @var	string	base url for files, null for not available
	 */
	protected $url = null;

	/**
	 * @var	array	contains file driver per file extension
	 */
	protected $file_drivers = array();

	protected function __construct(Array $config = array())
	{
		foreach ($config as $key => $value)
		{
			if (property_exists($this, $key))
			{
				$this->{$key} = $value;
			}
		}
	}

	public static function factory(Array $config = array())
	{
		return new static($config);
	}

	public function get_driver($path, Array $config)
	{
		if (is_file($path))
		{
			$info = pathinfo($path);
			if (array_key_exists($info['ext'], static::$file_drivers))
			{
				$class = static::$file_drivers[$info['ext']];
				return new $class($path, $config, $this);
			}

			return new File_Driver_File($path, $config, $this);
		}
		elseif (is_dir($path))
		{
			return new File_Driver_Directory($path, $config, $this);
		}
		else
		{
			throw new File_Exception('Invalid path for file or directory.');
		}
	}
}

/* End of file file.php */