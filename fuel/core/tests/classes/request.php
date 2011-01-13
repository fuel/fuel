<?php

namespace Fuel\Octane\Test;

use \Fuel\Core\Request;
use \Fuel\Octane\TestCase;

class RequestTest extends TestCase {

	public function test_uri_parse()
	{
		$this->assert_action(new Request('test', true), '');
		$this->assert_action(new Request('/test', true), '');
		$this->assert_action(new Request('test/index', true), 'index');
		$this->assert_action(new Request('/test/index', true), 'index');
		$this->assert_action(new Request('test/index/other/stuff', true), 'index');
		$this->assert_action(new Request('test/ind_ex/other/stuff', true), 'ind_ex');
	}

}
