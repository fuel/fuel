<?php

namespace Fuel\Octane\Test;

use \Fuel\Core\Html;
use \Fuel\Octane\TestCase;

class HtmlTest extends TestCase {

	public function test_h()
	{
		$output = Html::h('Example');
		$expected = "<h1>Example</h1>";
		$this->assert_equal($expected, $output);

		$output = Html::h('Some other example', '2', array('id' => 'h2', 'class' => 'sample', 'style' => 'color:red;'));
		$expected = "<h2 id=\"h2\" class=\"sample\" style=\"color:red;\">Some other example</h2>";
		$this->assert_equal($expected, $output);

		$attributes = array('id' => 'sample', 'class' => 'sample', 'style' => 'color:blue;');
		$output = Html::h('Variable!', '3', $attributes);
		$expected = "<h3 id=\"sample\" class=\"sample\" style=\"color:blue;\">Variable!</h3>";
		$this->assert_equal($expected, $output);
	}

	public function test_br()
	{
		$output = Html::br();
		$expected = "<br />";
		$this->assert_equal($expected, $output);

		$output = Html::br('2', array('id' => 'example', 'class' => 'sample', 'style' => 'color:red;'));
		$expected = "<br id=\"example\" class=\"sample\" style=\"color:red;\" /><br id=\"example\" class=\"sample\" style=\"color:red;\" />";
		$this->assert_equal($expected, $output);
	}

	public function test_hr()
	{
		$output = Html::hr();
		$expected = "<hr />";
		$this->assert_equal($expected, $output);

		$output = Html::hr(array('id' => 'example', 'class' => 'sample', 'style' => 'color:red;'));
		$expected = "<hr id=\"example\" class=\"sample\" style=\"color:red;\" />";
		$this->assert_equal($expected, $output);
	}

	public function test_title()
	{
		$output = Html::title();
		$expected = "<title></title>";
		$this->assert_equal($expected, $output);

		$output = Html::title('Some Title!');
		$expected = "<title>Some Title!</title>";
		$this->assert_equal($expected, $output);
	}

	public function test_nbs()
	{
		$output = Html::nbs();
		$expected = "&nbsp;";
		$this->assert_equal($expected, $output);

		$output = Html::nbs(2);
		$expected = "&nbsp;&nbsp;";
		$this->assert_equal($expected, $output);
	}

	public function test_meta()
	{
		$output = Html::meta('description', 'Meta Example!');
		$expected = "<meta name=\"description\" content=\"Meta Example!\" />";
		$this->assert_equal($expected, $output);

		$output = Html::meta('robots', 'no-cache');
		$expected = "<meta name=\"robots\" content=\"no-cache\" />";
		$this->assert_equal($expected, $output);

		$meta = array(
			array('name' => 'robots', 'content' => 'no-cache'),
			array('name' => 'description', 'content' => 'Meta Example'),
			array('name' => 'keywords', 'content' => 'fuel, rocks'),
			);

		$output = Html::meta($meta);
		$expected = "<meta name=\"robots\" content=\"no-cache\" /><meta name=\"description\" content=\"Meta Example\" /><meta name=\"keywords\" content=\"fuel, rocks\" />";
		$this->assert_equal($expected, $output);
	}

}

/* End of file html.php */
