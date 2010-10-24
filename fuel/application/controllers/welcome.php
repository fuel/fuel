<?php

class Welcome_Controller extends Controller {
	
	public function action_index()
	{
		$this->request->output = 'Hello from the the Welcome controller!';
	}
	
}