<?php

class Controller_Welcome extends Controller {
	
	public function action_index()
	{
		$this->request->output = 'Hello from the the Welcome controller!';
	}
	
	public function action_404()
	{
		$this->request->output = '404';
	}
}