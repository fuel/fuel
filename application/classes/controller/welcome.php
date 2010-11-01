<?php

class Controller_Welcome extends Controller {

	public function action_index()
	{
		$data['css'] = Asset::css(array('reset.css','960.css','main.css'));
		$data['controller_file'] = __FILE__;
		$this->request->output = View::factory('welcome',$data);
	}

	public function action_404()
	{
		$this->request->output = '404';
	}
}
