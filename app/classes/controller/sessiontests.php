<?php

class Controller_Sessiontests extends Controller {

	public function action_index()
	{
		$this->output = '<hr />Session Test controller!';
	}

	/*
	 * Test the dynamic use of the session
	 */
	public function action_dynamic()
	{
		$output = '';

		// start a new session
		$session = new Session();

		// testing sessions: get flash variable
		$f = $session->get_flash('variable');
		if ($f === false)
		{
			$output .= "flash variable: FALSE<br>";
		}
		else
		{
			$output .= "flash variable: ".$f."<br>";
		}

		// testing sessions: get / auto create/read
		$x = $session->get('counter');
		if ($x === false)
		{
			$output .= "get counter: FALSE<br>";
		}
		else
		{
			$output .= "get counter: ". $x."<br>";
		}

		$x = $x !== FALSE ? ++$x : 0;

		// testing sessions: set / auto create/read
		$session->set('counter', $x);
		$output .= "set counter: ". $x."<br>";

		// testing sessions: set flash variable
		$session->set_flash('variable', 'value '.$x);

		// testing sessions: write and destroy
		if ( $x >= 10 )
			$session->destroy();
		else
			$session->write();

		$this->output = $output;
	}

	/*
	 * Test the static use of the session
	 *
	 * Static calls use automatic read/write!
	 */
	public function action_static()
	{
		$output = '';

		// testing sessions: get flash variable
		$f = Session::get_flash('variable');
		if ($f === false)
		{
			$output .= "flash variable: FALSE<br>";
		}
		else
		{
			$output .= "flash variable: ".$f."<br>";
		}

		// testing sessions: get / auto create/read
		$x = Session::get('counter');
		if ($x === false)
		{
			$output .= "get counter: FALSE<br>";
		}
		else
		{
			$output .= "get counter: ". $x."<br>";
		}

		$x = $x !== FALSE ? ++$x : 0;

		// testing sessions: set / auto create/read
		Session::set('counter', $x);
		$output .= "set counter: ". $x."<br>";

		// testing sessions: set flash variable
		Session::set_flash('variable', 'value '.$x);

		// Destroy the session after 10 loads
		if ( $x >= 10 ) Session::destroy();

		$this->output = $output;
	}

	/*
	 * Controller 404
	 */
	public function action_404()
	{
		$this->output = '404 - Unknown action requested';
	}
}
