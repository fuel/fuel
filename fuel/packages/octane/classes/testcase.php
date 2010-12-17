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

use Fuel\App\Cli;
use Fuel\App\Fuel;
use Fuel\App\Request;

class TestCase {
	
	public $results = array();
	
	public function assert_equal($value, $expectation, $strict = true)
	{
		Tests::$results['assertions']++;
		$trace = debug_backtrace();
		
		if ($strict)
		{
			$result = ($value === $expectation);
		}
		else
		{
			$result = ($value == $expectation);
		}
		
		if ( ! $result)
		{
			$this->fail('assert_equal - Value "'.$value.'" does not equal "'.$expectation.'".');
		}
		else
		{
			$this->pass();
		}
	}

	public function assert_not_equal($value, $expectation, $strict = true)
	{
		Tests::$results['assertions']++;
		if ($strict)
		{
			$result = ($value !== $expectation);
		}
		else
		{
			$result = ($value != $expectation);
		}

		if ( ! $result)
		{
			$this->fail('assert_not_equal - Value "'.$value.'" equals "'.$expectation.'"');
		}
		else
		{
			$this->pass();
		}
	}

	public function assert_instance_of($value, $expectation)
	{
		Tests::$results['assertions']++;
		if ($value instanceof $expectation)
		{
			$this->pass();
		}
		else
		{
			$this->fail('assert_instance_of - Value "'.$value.'" is not and instance of "'.$expectation.'"');
		}
	}

	public function assert_action(Request $value, $expectation)
	{
		Tests::$results['assertions']++;
		if ($value->action == $expectation)
		{
			$this->pass();
		}
		else
		{
			$this->fail('assert_action - Given action "'.$value.'" does not match "'.$expectation.'"');
		}
	}

	public function pass()
	{
		$trace = debug_backtrace();
		
		// If the test has already failed then we don't want to set it to true.
		if (array_key_exists($this->results[$trace[2]['function']], $this->results)
		    && $this->results[$trace[2]['function']] === false)
		{
			return;
		}

		$this->results[$trace[2]['function']] = true;
	}

	public function fail($error)
	{
		$trace = debug_backtrace();
		$function = $trace[2]['function'];
		
		$this->results[$function] = false;
		
		Cli::write(Cli::color('Failure: '.$function, 'red'));
		Cli::write(Cli::color('    File: '.Fuel::clean_path($trace[1]['file']).' on line '.$trace[1]['line'], 'red'));
		Cli::write(Cli::color('    Error: '.$error, 'red'));
	}
}