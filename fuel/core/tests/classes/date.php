<?php

namespace Fuel\Octane\Test;

use \Fuel\Core\Date;
use \Fuel\Octane\TestCase;

class DateTest extends TestCase {

	public function pending_test_factory()
	{
	}

	public function pending_test_time()
	{
	}

	public function pending_test_create_from_string()
	{
	}

	public function pending_test_range_to_array()
	{
	}

	public function test_days_in_month()
	{
		$output = Date::days_in_month(2);
		$expected = 28;
		$this->assert_equal($expected, $output);

		$output = Date::days_in_month(2,2000);
		$expected = 29;
		$this->assert_equal($expected, $output);
	}

	public function test_format()
	{
		$output = Date::Factory( 1294176140 )->format("%m/%d/%Y");
		$expected = "01/04/2011";

		$this->assert_equal($expected, $output);
	}

	public function test_get_timestamp()
	{
		$output = Date::Factory( 1294176140 )->get_timestamp();
		$expected = 1294176140;

		$this->assert_equal($expected, $output);
	}

	public function test_get_timezone()
	{
		$output = Date::Factory( 1294176140, "Europe/London" )->get_timezone();
		$expected = "Europe/London";

		$this->assert_equal($expected, $output);
	}

	public function test_set_timezone()
	{
		$output = Date::Factory( 1294176140 )->set_timezone("America/Chicago")->get_timezone();
		$expected = "America/Chicago";
		
		$this->assert_equal($expected, $output);
	}

}

/* End of file date.php */
