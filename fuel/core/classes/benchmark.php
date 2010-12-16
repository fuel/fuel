<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Dan Horrigan <http://dhorrigan.com>
 * @license		Apache License v2.0
 * @copyright	2010 Dan Horrigan
 */

namespace Fuel\Core;

class Benchmark {

	protected static $benchmarks = array();

	final private function __construct() { }

	/**
	 * Starts a new benchmark.
	 *
	 * @param	string	The group name
	 * @param	string	The benchmark name
	 * @return	string
	 */
	public static function start($name)
	{
		static::$benchmarks[$name] = array (
			'start_time'   => microtime(TRUE),
			'start_memory' => memory_get_usage(),
			'stop_time'    => FALSE,
			'stop_memory'  => FALSE,
		);
	}

	/**
	 * Stops a benchmark.
	 *
	 * @param	string	The benchmark name
	 * @return	void
	 */
	public static function stop($name)
	{
		static::$benchmarks[$name]['stop_time']		= microtime(true);
		static::$benchmarks[$name]['stop_memory']	= memory_get_usage();
	}

	/**
	 * Deletes a benchmark.
	 *
	 * @param	string	The benchmark name
	 * @return	void
	 */
	public static function delete($token)
	{
		// Remove the benchmark
		unset(static::$benchmarks[$name]);
	}

	/**
	 * Gets the total execution time and memory usage of a benchmark as a list.
	 *
	 * @param   string  The benchmark name
	 * @return  array   execution time, memory
	 */
	public static function total($name)
	{
		return array (
			static::total_time($name),
			static::total_mem($name),
		);
	}

	/**
	 * Gets the total time elapsed for the specified benchmark
	 *
	 * @access	public
	 * @param	string	The benchmark name
	 * @return	float	The total elapsed time
	 */
	public static function total_time($name)
	{
		if ( ! isset(static::$benchmarks[$name]))
		{
			return false;
		}

		if (static::$benchmarks[$name]['stop_time'] === false)
		{
			static::stop($name);
		}

		return static::$benchmarks[$name]['stop_time'] - static::$benchmarks[$name]['start_time'];
	}

	/**
	 * Gets the total memory used for the specified benchmark
	 *
	 * @access	public
	 * @param	string	The benchmark name
	 * @return	float	The total memory used
	 */
	public static function total_mem($name)
	{
		if ( ! isset(static::$benchmarks[$name]))
		{
			return false;
		}

		if (static::$benchmarks[$name]['stop_memory'] === false)
		{
			static::stop($name);
		}

		return static::$benchmarks[$name]['stop_memory'] - static::$benchmarks[$name]['start_memory'];
	}

	public static function app_total()
	{
		static::$benchmarks['fuel.app'] = array (
			'start_time'   => FUEL_START_TIME,
			'start_memory' => FUEL_START_MEM,
		);
		static::stop('fuel.app');

		return static::total('fuel.app');
	}

}

/* End of file benchmark.php */