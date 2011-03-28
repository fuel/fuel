<?php
/**
 * An example Controller.  This shows the most basic usage of a Controller.
 */
class Controller_Welcome extends Controller {

	public function action_index()
	{
		$this->response->body = View::factory('welcome/index');
	}

	public function action_404()
	{
		$messages = array('Aw, crap!', 'Bloody Hell!', 'Uh Oh!', 'Nope, not here.', 'Huh?');
		$data['title'] = $messages[array_rand($messages)];

		// Set a HTTP 404 output header
		$this->response->status = 404;
		$this->response->body = View::factory('welcome/404', $data);
	}
}