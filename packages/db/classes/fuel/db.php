<?php defined('COREPATH') or die('No direct script access.');
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

class Fuel_DB {

	const SELECT = 1;
	const INSERT = 2;
	const UPDATE = 3;
	const DELETE = 4;

	public static $default = 'default';
	
	public static $instances = array();
	
	public static function instance($name = NULL, array $config = array())
	{
		if ($name === NULL)
		{
			$name = DB::$default;
		}

		if ( ! isset(DB::$instances[$name]))
		{
			if (empty($config))
			{
				if (($config = Config::get('db.'.$name)) === false)
				{
					Config::load('db', 'db');
					$config = Config::get('db.'.$name);
				}
			}
			$driver = 'DB_'.ucfirst($config['type']).'_Driver';
			
			DB::$instances[$name] = new $driver($name, $config);
		}

		return DB::$instances[$name];
	}

	public static function select()
	{
		return;
	}

	public static function insert()
	{
		return;
	}

	public static function update()
	{
		return;
	}

	public static function delete()
	{
		return;
	}
	
	public static function query($query, $type = NULL)
	{
		return new DB_Query($type, $query);
	}

}

/* End of file db.php */