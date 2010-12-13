<?php

namespace Fuel\Application\Controller;

use Fuel\Controller;

class Welcome extends Controller\Base {

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
