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

/**
 * Arr class tests
 * 
 * @group Core
 * @group Arr
 */
class Tests_Arr extends TestCase {

	/**
	 * Tests Arr::element()
	 * 
	 * @test
	 */
	public function test_element()
	{
		$person = array(
				"name" => "Jack",
				"age" => "21",
				"location" => array(
					"city" => "Pittsburgh",
					"state" => "PA",
					"country" => "US"
			)
		);

		$expected = "Jack";
		$output = Arr::element($person, "name", "Unknown Name");
		$this->assertEquals($expected, $output);

		$expected = "Unknown job";
		$output = Arr::element($person, "job", "Unknown job");
		$this->assertEquals($expected, $output);

		$expected = "Pittsburgh";
		$output = Arr::element($person, "location.city", "Unknown City");
		$this->assertEquals($expected, $output);

	}

	/**
	 * Tests Arr::flatten_assoc()
	 * 
	 * @test
	 */
	public function test_flatten_assoc()
	{
		$people = array(
			array(
				"name" => "Jack",
				"age" => 21
			),
			array(
				"name" => "Jill",
				"age" => 23
			)
		);

		$expected = array(
			"0:name" => "Jack",
			"0:age" => 21,
			"1:name" => "Jill",
			"1:age" => 23
		);

		$output = Arr::flatten_assoc($people);
		$this->assertEquals($expected, $output);
	}

	/**
	 * Tests Arr::insert()
	 * 
	 * @test
	 */
	public function test_insert()
	{
		$people = array("Jack", "Jill");

		$expected = array("Humpty", "Jack", "Jill");
		$output = Arr::insert($people, "Humpty", 0);

		$this->assertEquals(true, $output);
		$this->assertEquals($expected, $people);
	}

	/**
	 * Tests Arr::insert_after_key()
	 * 
	 * @test
	 */
	public function test_insert_after_key()
	{
		$people = array("Jack", "Jill");

		$expected = array("Jack", "Jill", "Humpty");
		$output = Arr::insert_after_key($people, "Humpty", 1);

		$this->assertEquals(true, $output);
		$this->assertEquals($expected, $people);
	}

	/**
	 * Tests Arr::insert_after_value()
	 * 
	 * @test
	 */
	public function test_insert_after_value()
	{
		$people = array("Jack", "Jill");

		$expected = array("Jack", "Humpty", "Jill");
		$output = Arr::insert_after_key($people, "Humpty", "Jack");

		$this->assertEquals(true, $output);
		$this->assertEquals($expected, $people);
	}

}

/* End of file arr.php */
