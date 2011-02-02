<?php

namespace Fuel\Octane\Test;

use \Fuel\Core\Uri;
use \Fuel\Octane\TestCase;

class UriTest extends TestCase {

	public function test_create()
	{
		$output = Uri::create('controller/method');
		$expected = "index.php/controller/method";
		$this->assert_equal($expected, $output);

		$output = Uri::create('controller/:some', array('some' => 'thing', 'and' => 'more'), array('what' => ':and'));
		$expected = "index.php/controller/thing?what=more";
		$this->assert_equal($expected, $output);
	}

/*

	# TODO: I need to learn how to mock stuff on PHP ...
	public function test_main()
	{
		$output = Uri::main();
		$expected = "index.php/";
		$this->assert_equal($expected, $output);
	}
*/

}

/* End of file uri.php */
