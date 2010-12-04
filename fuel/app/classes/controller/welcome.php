<?php

namespace Fuel\Application;

class Controller_Welcome extends Controller\Base {

	public $default_action = 'index';

	public function action_index()
	{
		$data['controller_file'] = Fuel::clean_path(__FILE__);

		$this->output = View::factory('welcome/index', $data);
	}

	public function action_404()
	{
		$data['controller_file'] = Fuel::clean_path(__FILE__);

		$this->output = View::factory('welcome/404', $data);
	}

}
