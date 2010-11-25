<?php

use Fuel\Controller;

class Controller_Welcome extends Controller\Base {

	public function action_index()
	{
		$data['controller_file'] = Fuel::clean_path(__FILE__);

		$bm = Benchmark::app_total();
		$data['exec_time'] = round($bm[0], 4);
		$data['mem_usage'] = round($bm[1] / pow(1024, 2), 3);

		$this->output = View::factory('welcome/index', $data);
	}

	public function action_404()
	{
		$data['controller_file'] = Fuel::clean_path(__FILE__);

		$bm = Benchmark::app_total();
		$data['exec_time'] = round($bm[0], 4);
		$data['mem_usage'] = round($bm[1] / pow(1024, 2), 3);

		$this->output = View::factory('welcome/404', $data);
	}
	
}
