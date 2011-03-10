<?php

/**
 * An example Controller.  This shows the most basic usage of a Controller.
 */
class Controller_Welcome extends Controller {

	public function action_index()
	{
		$this->render('welcome/index');
	}

	public function action_image($type = null)
	{
		$image = 'C:/wamp/www/test.jpeg';
		$watermark = 'C:/wamp/www/watermark.png';
		$mask = 'C:/wamp/www/mask.png';
		if ($type == 'im') {
			Image::factory(array(
				'driver' => 'imagemagick'
			))->load($image)->preset('test1', $watermark, $mask);
		} else if ($type == 'gd') {
			Image::factory(array(
				'driver' => 'gd'
			))->load($image)->preset('test1', $watermark, $mask);
		} else {
			echo "<div style=\"background: #000;\">" .
				"<img src='image/gd' style=\"margin: 10px;\" alt='' />" .
				"<img src='image/im' style=\"margin: 10px;\" alt='' />".
				"</div>";
		}
	}

	public function action_imagedebug($type = null)
	{
		$image = 'C:/wamp/www/test.jpeg';
		$watermark = 'C:/wamp/www/watermark.png';
		$mask = 'C:/wamp/www/mask.png';
		if ($type == 'im') {
			Image::factory(array(
				'driver' => 'imagemagick',
				'debug' => true
			))->load($image)->preset('test1', $watermark, $mask);
		} else if ($type == 'gd') {
			Image::factory(array(
				'driver' => 'gd',
				'debug' => true
			))->load($image)->preset('test1', $watermark, $mask);
		}
	}

	public function action_404()
	{
		// Set a HTTP 404 output header
		Output::$status = 404;
		$this->render('welcome/404');
	}

}