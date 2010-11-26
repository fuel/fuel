<?php

class Controller_Welcome extends Controller\Base {

	public function action_index()
	{
		Email::initalize(Array(
			'protocol' => 'smtp',
			'smtp_host' => 'localhost.com',
			'smtp_timeout' => 1,
			'send_multipart' => true
		));
		Email::to('kris@localhost.com')
			->from('admin@localhost.com')
			->message('Hello World!')
			->subject('Hello.')
			->send();
		Email::print_debugger();
	}

	public function action_404()
	{
		$data['controller_file'] = Fuel::clean_path(__FILE__);

		$this->output = View::factory('welcome/404', $data);
	}
	
}
