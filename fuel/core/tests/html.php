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
 * Html class tests
 * 
 * @group Core
 * @group Html
 */
class Tests_Html extends TestCase {

	/**
	 * Tests Html::h()
	 * 
	 * @test
	 */
	public function test_h()
	{
		$output = Html::h('Example');
		$expected = "<h1>Example</h1>";
		$this->assertEquals($expected, $output);

		$output = Html::h('Some other example', '2', array('id' => 'h2', 'class' => 'sample', 'style' => 'color:red;'));
		$expected = '<h2 id="h2" class="sample" style="color:red;">Some other example</h2>';
		$this->assertEquals($expected, $output);

		$attributes = array('id' => 'sample', 'class' => 'sample', 'style' => 'color:blue;');
		$output = Html::h('Variable!', '3', $attributes);
		$expected = '<h3 id="sample" class="sample" style="color:blue;">Variable!</h3>';
		$this->assertEquals($expected, $output);
	}

	/**
	 * Tests Html::br()
	 * 
	 * @test
	 */
	public function test_br()
	{
		$output = Html::br();
		$expected = "<br />";
		$this->assertEquals($expected, $output);

		$output = Html::br('2', array('id' => 'example', 'class' => 'sample', 'style' => 'color:red;'));
		$expected = '<br id="example" class="sample" style="color:red;" /><br id="example" class="sample" style="color:red;" />';
		$this->assertEquals($expected, $output);
	}

	/**
	 * Tests Html::hr()
	 * 
	 * @test
	 */
	public function test_hr()
	{
		$output = Html::hr();
		$expected = "<hr />";
		$this->assertEquals($expected, $output);

		$output = Html::hr(array('id' => 'example', 'class' => 'sample', 'style' => 'color:red;'));
		$expected = '<hr id="example" class="sample" style="color:red;" />';
		$this->assertEquals($expected, $output);
	}

	/**
	 * Tests Html::title()
	 * 
	 * @test
	 */
	public function test_title()
	{
		$output = Html::title();
		$expected = "<title></title>";
		$this->assertEquals($expected, $output);

		$output = Html::title('Some Title!');
		$expected = "<title>Some Title!</title>";
		$this->assertEquals($expected, $output);
	}

	/**
	 * Tests Html::nbs()
	 * 
	 * @test
	 */
	public function test_nbs()
	{
		$output = Html::nbs();
		$expected = "&nbsp;";
		$this->assertEquals($expected, $output);

		$output = Html::nbs(2);
		$expected = "&nbsp;&nbsp;";
		$this->assertEquals($expected, $output);
	}

	/**
	 * Tests Html::meta()
	 * 
	 * @test
	 */
	public function test_meta()
	{
		$output = Html::meta('description', 'Meta Example!');
		$expected = '<meta name="description" content="Meta Example!" />';
		$this->assertEquals($expected, $output);

		$output = Html::meta('robots', 'no-cache');
		$expected = '<meta name="robots" content="no-cache" />';
		$this->assertEquals($expected, $output);

		$meta = array(
			array('name' => 'robots', 'content' => 'no-cache'),
			array('name' => 'description', 'content' => 'Meta Example'),
			array('name' => 'keywords', 'content' => 'fuel, rocks'),
			);

		$output = Html::meta($meta);
		$expected = '
<meta name="robots" content="no-cache" />
<meta name="description" content="Meta Example" />
<meta name="keywords" content="fuel, rocks" />';
		$this->assertEquals($expected, $output);
	}

}

/* End of file html.php */
