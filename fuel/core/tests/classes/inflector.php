<?php

namespace Fuel\Octane\Test;

use \Fuel\Core\Inflector;
use \Fuel\Octane\TestCase;

class InflectorTest extends TestCase {
	
	public function test_denamespace()
	{
		$this->assert_equal(Inflector::denamespace('Fuel\\SomeClass'), 'SomeClass');
		$this->assert_equal(Inflector::denamespace('\\SomeClass'), 'SomeClass');
		$this->assert_equal(Inflector::denamespace('SomeClass'), 'SomeClass');
		$this->assert_equal(Inflector::denamespace('SomeClass\\'), 'SomeClass');
	}

	public function test_tableize()
	{
		$this->assert_equal(Inflector::tableize('\\Model\\User'), 'users');
		$this->assert_equal(Inflector::tableize('\\Model\\Person'), 'people');
		$this->assert_equal(Inflector::tableize('\\Model\\Mouse'), 'mice');
		$this->assert_equal(Inflector::tableize('\\Model\\Ox'), 'oxen');
		$this->assert_equal(Inflector::tableize('\\Model\\Matrix'), 'matrices');
	}
	
}
