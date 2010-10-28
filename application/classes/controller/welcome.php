<?php

class Controller_Welcome extends Controller {

	public function action_index()
	{
		$session = Session::instance();
		$session->read();
		$x = $session->get('counter');
		$x = $x !== FALSE ? ++$x : 0;
		$session->set('counter', $x);
		echo "Visit ", $x,"<br>";
		$session->write();
//	$session->destroy();

		$this->request->output = '<hr />Hello from the the Welcome controller!';
	}

	public function action_404()
	{
		$this->request->output = '404';
	}
}
