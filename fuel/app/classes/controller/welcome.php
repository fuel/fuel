<?php

namespace Fuel\Application\Controller;

class Welcome extends Controller\Template {

	public $default_action = 'index';

	public function action_index()
	{
		$this->template->title = 'FUEL';
		$this->render('index', array(
			'controller_file' => Fuel::clean_path(__FILE__)
		));
	}

	public function action_404()
	{
		$this->template->title = 'Page Not Found';
		$this->render('404', array(
			'controller_file' => Fuel::clean_path(__FILE__)
		));
	}

}
