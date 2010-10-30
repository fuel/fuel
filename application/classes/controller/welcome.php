<?php

class Controller_Welcome extends Controller {

	public function action_index()
	{
		// testing sessions: create / read
		Session::read();

		// testing sessions: get flash variable
		$f = Session::get_flash('variable');
		if ($f === false)
		{
			echo "flash variable: FALSE<br>";
		}
		else
		{
			echo "flash variable: ",$f,"<br>";
		}

		// testing sessions: get / auto create/read
		$x = Session::get('counter');
		if ($x === false)
		{
			echo "get counter: FALSE<br>";
		}
		else
		{
			echo "get counter: ", $x,"<br>";
		}

		$x = $x !== FALSE ? ++$x : 0;

		// testing sessions: set / auto create/read
		Session::set('counter', $x);
		echo "set counter: ", $x,"<br>";

		// testing sessions: set flash variable
		Session::set_flash('variable', 'value '.$x);

		// testing sessions: write
		Session::write();

		// testing sessions: destroy
		if ( $x == 10 ) Session::destroy();

		$this->request->output = '<hr />Hello from the the Welcome controller!';
	}

	public function action_404()
	{
		$this->request->output = '404';
	}
}
