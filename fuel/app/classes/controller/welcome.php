<?php

class Controller_Welcome extends Controller {

	public $default_action = 'index';

	public function action_index()
	{
		$this->render('welcome/index');
	}

	public function action_404()
	{
		$this->render('welcome/404');
	}

}
