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

namespace Fuel\Octane;

use Fuel\Application\Cli;

class Tests {
	
	public static $results = array(
		'passes'		=> 0,
		'failures'		=> 0,
		'assertions'	=> 0,
	);
	
	public static function run_all($args)
	{
		static::header('All Tests');
		$directories = array(
			COREPATH.'tests/classes',
		);
		$classes = array();
		// Include all the test files from all of the directories
		foreach ( $directories as $dir )
		{
			foreach ( glob($dir . '/*.php') as $filename )
			{
				if ( $filename != __FILE__ )
				{
					$classes[] = basename($filename, '.php');
					require_once( $filename );
				}
			}
		}
		
		foreach ($classes as $class)
		{
			$class = '\\Fuel\\Octane\\Test\\'.ucfirst($class).'Test';
			
			$test = new $class;
			$methods = get_class_methods($test);
			
			foreach ($methods as $method)
			{
				if (strncmp($method, 'test_', 5) !== 0)
				{
					continue;
				}
				$test->$method();
				if ($test->results[$method])
				{
					static::$results['passes']++;
				}
				else
				{
					static::$results['failures']++;
				}
			}
		}
		
		$passes = Cli::color('Passes: '.static::$results['passes'], 'green');
		$failures = Cli::color('Failures: '.static::$results['failures'], 'red');
		$assertions = 'Assertions: '.static::$results['assertions'];
		Cli::write();
		Cli::write($passes.' | '.$failures.' | '.$assertions);
		
	}
	
	public static function header($description = '')
	{
		Cli::write('-------------------------------------------------');
		Cli::write(' Octane Unit Testing');
		Cli::write(' Running Test: '.$description);
		Cli::write('-------------------------------------------------');
		Cli::write();
	}
	
}