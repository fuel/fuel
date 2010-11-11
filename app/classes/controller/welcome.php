<?php

class Controller_Welcome extends Controller {

	public function action_index()
	{
		$data['css'] = Asset::css(array('reset.css','960.css','main.css'));
		$data['controller_file'] = str_replace(DOCROOT, '', __FILE__);
		
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
		$this->output = 'Hello '.$this->params('name');
	}
}
