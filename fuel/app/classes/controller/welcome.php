<?php
/**
 * An example Controller.  This shows the most basic usage of a Controller.
 */
class Controller_Welcome extends Controller {

	public function action_index()
	{
		$this->render('welcome/index');
	}

	public function action_image()
	{
		echo "<div style='background: black'><img src='imageview' alt='' /></div>";
	}

	public function action_imageview()
	{
		$image = Image::factory();
		$image->load('C:/wamp/www/test.jpeg')->mask('C:/wamp/www/mask.png')->output('png');
	}

	public function action_404()
	{
		// Set a HTTP 404 output header
		Output::$status = 404;
		$this->render('welcome/404');
	}
}