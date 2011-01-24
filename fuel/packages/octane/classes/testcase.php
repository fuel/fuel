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
 * @copyright	2010 - 2011 Fuel Development Team
 * @link		http://fuelphp.com
 */

namespace Fuel\Octane;

use \Cli;
use \Fuel;
use \Fuel\Core\Request;

class TestCase {

	public $results = array();

	/**
	 * Test if a boolean expression validates as true or if a value is non-empty (when strict is false)
	 *
	 * @param	bool|mixed
	 * @param	bool
	 */
	public function assert_true($value, $strict = true)
	{
		Tests::$results['assertions']++;

		if ($value === true or ( ! $strict and $value))
		{
			$this->pass();
		}
		else
		{
			$this->fail('assert_true - Value is does not validate as true.');
		}
	}

	/**
	 * Test if a boolean expression validates as false or if a value is empty (when strict is false)
	 *
	 * @param	bool|mixed
	 * @param	bool
	 */
	public function assert_false($value, $strict = true)
	{
		Tests::$results['assertions']++;

		if ($value === false or ( ! $strict and ! $value))
		{
			$this->pass();
		}
		else
		{
			$this->fail('assert_false - Value is does not validate as false.');
		}
	}

	/**
	 * Test if the first value is equal to the second
	 *
	 * @param	mixed
	 * @param	mixed
	 * @param	bool
	 */
	public function assert_equal($value, $expectation, $strict = true)
	{
		Tests::$results['assertions']++;

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

	/**
	 * Test if the first value is not equal to the second
	 *
	 * @param	mixed
	 * @param	mixed
	 * @param	bool
	 */
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

	/**
	 * Test if the object is an instance of the given class
	 *
	 * @param	object
	 * @param	string
	 */
	public function assert_instance_of($value, $expectation)
	{
		Tests::$results['assertions']++;
		if ($value instanceof $expectation)
		{
			$this->pass();
		}
		else
		{
			$this->fail('assert_instance_of - Value is not an instance of "'.$expectation.'", it is an instance of "'.get_class($value).'".');
		}
	}

	/**
	 * Test if the value is of a certain type
	 *
	 * @param	object
	 * @param	string
	 */
	public function assert_type_of($value, $expectation)
	{
		Tests::$results['assertions']++;

		$type_aliasses = array('bool' => 'boolean', 'int' => 'integer', 'float' => 'double');
		array_key_exists($expectation, $type_aliasses) and $expectation = $type_aliasses[$expectation];

		if (gettype($value) == $expectation)
		{
			$this->pass();
		}
		else
		{
			$this->fail('assert_type_of - Value is not of type "'.$expectation.'", but instead  "'.gettype($value).'".');
		}
	}

	/**
	 * Tests whether the given callback or closure executes without throwing an exception
	 *
	 * @param	callback
	 * @param	array
	 */
	public function assert_no_exception($value, $args = array())
	{
		Tests::$results['assertions']++;

		if ( ! is_callable($value))
		{
			$this->fail('assert_no_exception - Value passed is not a valid callback.');
		}

		try
		{
			call_user_func_array($value, (array) $args);
			$this->pass();
		}
		catch (\Exception $e)
		{
			$this->fail('assert_no_exception - Exception thrown.');
		}
	}

	/**
	 * Tests whether the given callback or closure results in an exception being thrown
	 *
	 * @param	callback
	 * @param	array
	 */
	public function assert_exception($value, $args = array())
	{
		Tests::$results['assertions']++;

		if ( ! is_callable($value))
		{
			$this->fail('assert_exception - Value passed is not a valid callback.');
		}

		try
		{
			call_user_func_array($value, (array) $args);
			$this->fail('assert_exception - No exception thrown.');
		}
		catch (\Exception $e)
		{
			$this->pass();
		}
	}

	/**
	 * Test if the given Request object will execute the given action
	 *
	 * @param	Request
	 * @param	string
	 */
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

	/**
	 * Pass the current test
	 */
	public function pass()
	{
		$trace = debug_backtrace();

		// If the test has already failed then we don't want to set it to true.
		if ( ! empty($trace[2]['function']) and (is_int($trace[2]['function']) or is_string($trace[2]['function']))
			and @array_key_exists($trace[2]['function'], $this->results)
		    and $this->results[$trace[2]['function']] === false)
		{
			return;
		}

		$this->results[$trace[2]['function']] = true;
	}

	/**
	 * Fail the current test
	 */
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

/* end of file testcase.php */