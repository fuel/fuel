<?php

namespace Fuel\Octane\Test;

class InflectorTest extends Octane\TestCase {
	
	public function test_denamespace()
	{
		$this->assert_equal(Inflector::denamespace('Fuel\\SomeClass'), 'SomeClass');
		$this->assert_equal(Inflector::denamespace('\\SomeClass'), 'SomeClass');
		$this->assert_equal(Inflector::denamespace('SomeClass'), 'SomeClass');
		$this->assert_equal(Inflector::denamespace('SomeClass\\'), 'SomeClass');
	}

	public function test_tableize()
	{
		$this->assert_equal(Inflector::tableize('Fuel\\App\\Model\\User'), 'users');
		$this->assert_equal(Inflector::tableize('Fuel\\App\\Model\\Person'), 'people');
		$this->assert_equal(Inflector::tableize('Fuel\\App\\Model\\Mouse'), 'mice');
		$this->assert_equal(Inflector::tableize('Fuel\\App\\Model\\Ox'), 'oxen');
		$this->assert_equal(Inflector::tableize('Fuel\\App\\Model\\Matrix'), 'matrices');
		// TODO: Write more tests
	}
	
}