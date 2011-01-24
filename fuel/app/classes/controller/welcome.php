<?php
/**
 * An example Controller.  This shows the most basic usage of a Controller.
 */
class Controller_Welcome extends Controller_Template {

	public function action_index()
	{
		$this->template->css = \Asset::css(array('style2.css'), array(), 'layout', false);

		$this->template->title = 'foo';
		$this->template->content = View::factory('welcome/index');
	}

	public function action_404()
	{
		// Set a HTTP 404 output header
		Output::$status = 404;
		$this->render('welcome/404');
	}
}