<?php
/**
 * An example Controller.  This shows the most basic usage of a Controller.
 */
class Controller_Sample extends Controller {

	public function action_index()
	{
		\Output::$status = 200;
		list($output, $status) = Connector::factory("GET welcome/sample", array('hello' => 'world'))->execute();
	}

	public function action_404()
	{
		// Set a HTTP 404 output header
		Output::$status = 404;
		$this->render('welcome/404');
	}
}