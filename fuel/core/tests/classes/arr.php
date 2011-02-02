<?php

namespace Fuel\Octane\Test;

use \Fuel\Core\Arr;
use \Fuel\Octane\TestCase;

class ArrTest extends TestCase {

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

		$output = Arr::flatten_assoc($people);

		$expected = array(
									"0:name" => "Jack",
									"0:age" => 21,
									"1:name" => "Jill",
									"1:age" => 23
								);

		$output = Arr::flatten_assoc($people);
		$this->assert_equal($expected, $output);
	}

	// public static function element($array, $key, $default = false)
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
		$this->assert_equal($expected, $output);

		$expected = "Unknown job";
		$output = Arr::element($person, "job", "Unknown job");
		$this->assert_equal($expected, $output);

		$expected = "Pittsburgh";
		$output = Arr::element($person, "location.city", "Unknown City");
		$this->assert_equal($expected, $output);

	}

/*

	# TODO
	public function test_elements()
	{
		return true;
	}

*/

	public function test_insert()
	{
		$people = array("Jack", "Jill");

		$expected = array("Humpty", "Jack", "Jill");
		$output = Arr::insert($people, "Humpty", 0);

		$this->assert_equal(true, $output);
		$this->assert_equal($expected, $people);
	}

	public function test_insert_after_key()
	{
		$people = array("Jack", "Jill");

		$expected = array("Jack", "Jill", "Humpty");
		$output = Arr::insert_after_key($people, "Humpty", 1);

		$this->assert_equal(true, $output);
		$this->assert_equal($expected, $people);
	}

	public function test_insert_after_value()
	{
		$people = array("Jack", "Jill");

		$expected = array("Jack", "Humpty", "Jill");
		$output = Arr::insert_after_key($people, "Humpty", "Jack");

		$this->assert_equal(true, $output);
		$this->assert_equal($expected, $people);
	}

}

/* End of file arr.php */
