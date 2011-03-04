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
		Image::load('C:/wamp/www/test.jpeg')
				->border(20, '#000000')
				->rounded(20, null, 1)
				//->rotate(90)
				//->mask('C:/wamp/www/mask.png')
				->resize('50%')
				->output('png')
				// ->save('C:/wamp/www/solidblock-modified.jpeg')
				;
	}

	public function action_404()
	{
		// Set a HTTP 404 output header
		Output::$status = 404;
		$this->render('welcome/404');
	}
}