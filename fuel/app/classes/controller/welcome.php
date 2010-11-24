<?php

use Fuel\Controller;

class Controller_Welcome extends Controller\Base {

	public function action_index()
	{
		$data['css'] = Asset::css(array('reset.css','960.css','main.css'));
		$data['controller_file'] = __FILE__;

		$bm = Benchmark::app_total();
		$data['exec_time'] = round($bm[0], 4);
		$data['mem_usage'] = round($bm[1] / pow(1024, 2), 4);

		$this->output = View::factory('welcome/index', $data);
	}

	public function action_404()
	{
		$data['css'] = Asset::css(array('reset.css','960.css','main.css'));
		$data['controller_file'] = __FILE__;

		$this->output = View::factory('welcome/404', $data);
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
		
		$this->output = View::factory('welcome/pagination', $data);
	}
}
