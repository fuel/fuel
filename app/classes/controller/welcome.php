<?php

use Fuel\Controller;

class Controller_Welcome extends Controller\Base {

	public function action_index()
	{
		$data['css'] = Asset::css(array('reset.css','960.css','main.css'));
		$data['controller_file'] = str_replace(DOCROOT, '', __FILE__);

		$bm = Benchmark::app_total();
		$data['exec_time'] = round($bm[0], 4);
		$data['mem_usage'] = round($bm[1] / pow(1024, 2), 4);

		$this->output = View::factory('welcome/index', $data);
	}

	public function action_404()
	{
		$data['css'] = Asset::css(array('reset.css','960.css','main.css'));
		$data['controller_file'] = str_replace(DOCROOT, '', __FILE__);

		$this->output = View::factory('welcome/404', $data);
	}
	
	public function action_hello()
	{
		$this->output = 'Hello '.$this->param('name');
	}
}
