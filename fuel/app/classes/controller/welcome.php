<?php
/**
 * An example Controller.  This shows the most basic usage of a Controller.
 */
class Controller_Welcome extends Controller {

	public function action_index()
	{
		$this->render('welcome/index');
	}

	public function action_404()
	{
		$this->render('welcome/404');
	}

}
