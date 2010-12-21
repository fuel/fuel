<?php

namespace Fuel\App\Controller;

class Welcome extends BaseController {

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
