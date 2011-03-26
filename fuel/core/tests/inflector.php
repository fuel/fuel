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
 * Inflector class tests
 *
 * @group Core
 * @group Inflector
 */
class Tests_Inflector extends TestCase {

	/**
	 * Test for Inflector::ascii()
	 *
	 * @test
	 */
	public function test_ascii()
	{
		$output = Inflector::ascii('Inglés');
		$expected = "Ingles";
		$this->assertEquals($expected, $output);
	}

	/**
	 * Test for Inflector::camelize()
	 *
	 * @test
	 */
	public function test_camelize()
	{
		$output = Inflector::camelize('apples_and_oranges');
		$expected = 'Apples_And_Oranges';
		$this->assertEquals($expected, $output);
	}

	/**
	 * Test for Inflector::classify()
	 *
	 * @test
	 */
	public function test_classify()
	{
		$output = Inflector::classify('fuel_users');
		$expected = 'Fuel_User';
		$this->assertEquals($expected, $output);
	}

	/**
	 * Test for Inflector::demodulize()
	 *
	 * @test
	 */
	public function test_demodulize()
	{
		$output = Inflector::demodulize('Uri::main()');
		$expected = 'main()';
		$this->assertEquals($expected, $output);
	}

	/**
	 * Test for Inflector::denamespace()
	 *
	 * @test
	 */
	public function test_denamespace()
	{
		$this->assertEquals(Inflector::denamespace('Fuel\\SomeClass'), 'SomeClass');
		$this->assertEquals(Inflector::denamespace('\\SomeClass'), 'SomeClass');
		$this->assertEquals(Inflector::denamespace('SomeClass'), 'SomeClass');
		$this->assertEquals(Inflector::denamespace('SomeClass\\'), 'SomeClass');
	}

	/**
	 * Test for Inflector::foreign_key()
	 *
	 * @test
	 */
	public function test_foreign_key()
	{
		$output = Inflector::foreign_key('Inflector');
		$expected = 'inflector_id';
		$this->assertEquals($expected, $output);

		$output = Inflector::foreign_key('Inflector', false);
		$expected = 'inflectorid';
		$this->assertEquals($expected, $output);
	}

	/**
	 * Test for Inflector::friendly_title()
	 *
	 * @test
	 */
	public function test_friendly_title()
	{
		$output = Inflector::friendly_title('Fuel is a community driven PHP 5 web framework.', '-', true);
		$expected = 'fuel-is-a-community-driven-php-5-web-framework';
		$this->assertEquals($expected, $output);
	}

	/**
	 * Test for Inflector::humanize()
	 *
	 * @test
	 */
	public function test_humanize()
	{
		$output = Inflector::humanize('apples_and_oranges');
		$expected = 'Apples and oranges';
		$this->assertEquals($expected, $output);
	}

	/**
	 * Test for Inflector::is_countable()
	 *
	 * @test
	 */
	public function test_is_countable()
	{
		$output = Inflector::is_countable('fish');
		$this->assertFalse($output);

		$output = Inflector::is_countable('apple');
		$this->assertTrue($output);
	}

	/**
	 * Test for Inflector::pluralize()
	 *
	 * @test
	 */
	public function test_pluralize()
	{
		$output = Inflector::pluralize('apple');
		$expected = "apples";
		$this->assertEquals($expected, $output);
	}

	/**
	 * Test for Inflector::singularize()
	 *
	 * @test
	 */
	public function test_singularize()
	{
		$output = Inflector::singularize('apples');
		$expected = "apple";
		$this->assertEquals($expected, $output);
	}

	/**
	 * Test for Inflector::tableize()
	 *
	 * @test
	 */
	public function test_tableize()
	{
		$this->assertEquals(Inflector::tableize('\\Model\\User'), 'users');
		$this->assertEquals(Inflector::tableize('\\Model\\Person'), 'people');
		$this->assertEquals(Inflector::tableize('\\Model\\Mouse'), 'mice');
		$this->assertEquals(Inflector::tableize('\\Model\\Ox'), 'oxen');
		$this->assertEquals(Inflector::tableize('\\Model\\Matrix'), 'matrices');
	}

	/**
	 * Test for Inflector::underscore()
	 *
	 * @test
	 */
	public function test_underscore()
	{
		$output = Inflector::underscore('ApplesAndOranges');
		$expected = "apples_and_oranges";
		$this->assertEquals($expected, $output);
	}
}

/* End of file inflector.php */