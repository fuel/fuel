<?php

class Controller_Test extends Fuel\Controller\Base {

	public function action_index()
	{
		$data['css'] = Asset::css(array('reset.css','960.css','main.css'));
		$data['controller_file'] = Fuel::clean_path(__FILE__);

		$bm = Benchmark::app_total();
		$data['exec_time'] = round($bm[0], 4);
		$data['mem_usage'] = round($bm[1] / pow(1024, 2), 4);

		$this->output = View::factory('test/index', $data);
	}

	public function action_404()
	{
		$data['css'] = Asset::css(array('reset.css','960.css','main.css'));
		$data['controller_file'] = Fuel::clean_path(__FILE__);

		$this->output = View::factory('test/404', $data);
	}

	public function action_hello()
	{
		$this->output = 'Hello '.$this->param('name');
	}

	public function action_pagination()
	{
		$count = DB::select(DB::expr('COUNT(*) AS mycount'))->from('users')->execute()->get('mycount');

		$config = array(
			'total_items' => $count,
			'per_page' => 5,
			'pagination_url' => 'welcome/pagination',
			'uri_segment' => 3,
			'num_links' => 5, // this is not required and is the number of links on each side of the current page
		);

		Pagination::set_config($config);

		$items = DB::select('id', 'username')->from('users')->limit(Pagination::$per_page)->offset(Pagination::$offset)->execute()->as_array();

		$data['items'] = $items;

		// Create links
		$data['pagination'] = Pagination::create_links();

		$data['css'] = Asset::css(array('reset.css','960.css','main.css'));
		$data['controller_file'] = __FILE__;

		$this->output = View::factory('test/pagination', $data);
	}

	public function action_validation()
	{
		$input = array('test' => '   Fuel ', 'empty' => '');
		Validation::add_field('test', 'Testfield', array('trim', 'required', function($val) { return $val.'PHP'; } ));
		Validation::add_field('empty', 'Empty field', array('required'));
		Validation::run($input);
		echo '<pre>';
		var_dump(Validation::validated());
		foreach(Validation::errors() as $e)
			echo "\t".$e."\n";
		exit('</pre>');
	}
}
