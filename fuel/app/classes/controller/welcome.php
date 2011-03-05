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
		echo "<div style='background: black; padding: 10px;'><img src='imageview' alt='' /></div>";
	}

	public function action_imageview()
	{
		Image::load('C:/wamp/www/test.png')
				//->mask('C:/wamp/www/mask.png')
				->crop('25%', '25%', '75%', '75%')
				->resize('200%')
				->border(20, '#FF0000')
				->rounded(10, null, 1)
				->rotate(90)
				->watermark('C:/wamp/www/watermark.png', 'bottom left')
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