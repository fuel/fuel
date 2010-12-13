<?php

namespace Fuel\Octane\Test;

class RequestTest extends Octane\TestCase {

	public function test_uri_parse()
	{
		$this->assert_action(new Request('test'), '');
		$this->assert_action(new Request('/test'), '');
		$this->assert_action(new Request('test/index'), 'index');
		$this->assert_action(new Request('/test/index'), 'index');
		$this->assert_action(new Request('test/index/other/stuff'), 'index');
		$this->assert_action(new Request('test/ind_ex/other/stuff'), 'ind_ex');
	}

}