<?php

namespace Fuel\Octane\Test;

use \Fuel\Core\Inflector;
use \Fuel\Octane\TestCase;

class InflectorTest extends TestCase {

	public function test_ascii()
	{
		$output = Inflector::ascii('Inglés');
		$expected = "Ingles";
		$this->assert_equal($expected, $output);
	}

	public function test_camelize()
	{
		$output = Inflector::camelize('apples_and_oranges');
		$expected = 'ApplesAndOranges';
		$this->assert_equal($expected, $output);
	}

	public function test_classify()
	{
		$output = Inflector::classify('fuel_users');
		$expected = 'FuelUser';
		$this->assert_equal($expected, $output);
	}

	public function test_demodulize()
	{
		$output = Inflector::demodulize('Uri::main()');
		$expected = 'main()';
		$this->assert_equal($expected, $output);
	}

	public function test_denamespace()
	{
		$this->assert_equal(Inflector::denamespace('Fuel\\SomeClass'), 'SomeClass');
		$this->assert_equal(Inflector::denamespace('\\SomeClass'), 'SomeClass');
		$this->assert_equal(Inflector::denamespace('SomeClass'), 'SomeClass');
		$this->assert_equal(Inflector::denamespace('SomeClass\\'), 'SomeClass');
	}

	public function test_foreign_key()
	{
		$output = Inflector::foreign_key('Inflector');
		$expected = 'inflector_id';
		$this->assert_equal($expected, $output);

		$output = Inflector::foreign_key('Inflector', false);
		$expected = 'inflectorid';
		$this->assert_equal($expected, $output);
	}

	public function test_friendly_title()
	{
		$output = Inflector::friendly_title('Fuel is a community driven PHP 5 web framework.', '-', true);
		$expected = 'fuel-is-a-community-driven-php-5-web-framework';
		$this->assert_equal($expected, $output);
	}

	public function test_humanize()
	{
		$output = Inflector::humanize('apples_and_oranges');
		$expected = 'Apples and oranges';
		$this->assert_equal($expected, $output);
	}

	public function test_is_countable()
	{
		$output = Inflector::is_countable('fish');
		$this->assert_false($output);

		$output = Inflector::is_countable('apple');
		$this->assert_true($output);
	}

	public function test_pluralize()
	{
		$output = Inflector::pluralize('apple');
		$expected = "apples";
		$this->assert_equal($expected, $output);
	}

	public function test_singularize()
	{
		$output = Inflector::singularize('apples');
		$expected = "apple";
		$this->assert_equal($expected, $output);
	}

	public function test_tableize()
	{
		$this->assert_equal(Inflector::tableize('\\Model\\User'), 'users');
		$this->assert_equal(Inflector::tableize('\\Model\\Person'), 'people');
		$this->assert_equal(Inflector::tableize('\\Model\\Mouse'), 'mice');
		$this->assert_equal(Inflector::tableize('\\Model\\Ox'), 'oxen');
		$this->assert_equal(Inflector::tableize('\\Model\\Matrix'), 'matrices');
	}

	public function test_underscore()
	{
		$output = Inflector::underscore('ApplesAndOranges');
		$expected = "apples_and_oranges";
		$this->assert_equal($expected, $output);
	}

}
