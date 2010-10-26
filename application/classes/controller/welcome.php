<?php

class Controller_Welcome extends Controller {
	
	public function action_index()
	{
		$this->request->output = 'Hello from the the Welcome controller!';
	}
	
}