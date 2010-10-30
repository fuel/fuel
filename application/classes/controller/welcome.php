<?php

class Controller_Welcome extends Controller {

	public function action_index()
	{
		$this->request->output = '<hr />Hello from the the Welcome controller!';

		Lang::load('test');
		$this->template->title = Lang::__('hello', array('name' => 'gary'));
		$this->template->content = View::factory('test');
	}

	public function action_404()
	{
		$this->request->output = '404';
	}
}
