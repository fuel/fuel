<?php

/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * Image manipulation class.
 *
 * @package		Fuel
 * @version		1.0
 * @author		DudeAmI aka Kris <dudeami0@gmail.com>
 * @license		MIT License
 * @copyright	2010 - 2011 Fuel Development Team
 * @link		http://fuelphp.com
 */

namespace Fuel\Core;

class Image_Imagemagick extends Image_Driver {

	private $image_temp = null;

	protected $accepted_extensions = array('png', 'gif', 'jpg', 'jpeg');

	public function _load($return_data)
	{
		if (empty($this->image_temp)) {
			do {
				$this->image_temp = substr($this->config['temp_dir'] . $this->config['temp_append'] . md5(time() * microtime()), 0, 32);
			} while (file_exists($this->image_temp));
		}
		copy($this->image_fullpath, $this->image_temp);
		$this->debug($this->image_fullpath . '<br />' . $this->image_temp);
	}

	public function _crop($x1, $y1, $x2, $y2)
	{
		extract(parent::_crop($x1, $y1, $x2, $y2));
		$image = '"' . $this->image_temp . '"';
		$this->exec('convert', $image . ' -crop ' . ($x2 - $x1) . 'x' . ($y2 - $y1) . '-' . $x1 . '-' . $y1 . ' ' . $image);
	}

	public function _resize($width, $height = null, $keepar = true, $pad = true)
	{
		extract(parent::_resize($width, $height, $keepar, $pad));
		$image = '"' . $this->image_temp . '"';
		$this->exec('convert', $image . " -resize " . $width . "x" . $height . ($keepar ? "!" : "") . " " . $image);
	}

	public function _rotate($degrees)
	{
		extract(parent::_rotate($degrees));
		$color = $this->create_color($this->config['bgcolor'], 0);
		$image = '"' . $this->image_temp . '"';
		$this->exec('convert', $image . " -background " . $color . " -rotate " . $degrees . " " . $image);
	}

	public function _watermark($filename, $x, $y)
	{
		extract(parent::_watermark($filename, $x, $y));
		$wsizes = $this->sizes($filename);
		$image = '"' . $this->image_temp . '"';
		$filename = '"' . $filename . '"';
		$this->exec(
				'composite',
				'-compose atop -geometry +' . $x . '+' . $y . ' ' .
				'-dissolve ' . $this->config['watermark_alpha'] . '% ' .
				$filename . ' ' . $image . ' ' . $image
		);
	}

	public function sizes($filename = null)
	{
		if (empty($filename) && !empty($this->image_temp))
			$filename = $this->image_temp;
		$width = null;
		$height = null;
		$output = $this->exec('identify', '-format "%[fx:w] %[fx:h]" "' . $filename . '"');
		list($width, $height) = explode(" ", $output[0]);
		return (object) array(
			'width' => $width,
			'height' => $height
		);
	}

	public function _save($filename, $permissions = null)
	{
		extract(parent::output($filename, $permissions));
		$this->run_queue();
		$old = '"' . $this->image_temp . '"';
		$new = '"' . $filename . '"';
		$this->exec('convert', $old . ' ' . $new);
	}

	public function output($filetype = null)
	{
		extract(parent::output($filetype));
		$this->run_queue();
		if (substr($this->image_fullpath, -1 * strlen($filetype)) != $filetype) {
			$old = '"' . $this->image_temp . '"';
			passthru('convert ' . $old . ' ' . strtolower($filetype) . ':');
		} else {
			echo file_get_contents($this->image_temp);
		}
	}

	public function exec($program, $params) {
		$command = realpath($this->config['imagemagick_dir'] . $program) . " " . $params;
		$this->debug("Running command: <span style='font-family: courier;'>$command</span>");
		exec($command, $output, $code);
		if ($code != 0) {
			// Try to come up with a common error?
			if (!file_exists(realpath($this->config['imagemagick_dir'] . $program))) {
				$this->error("imagemagick executable not found in " . $this->config['imagemagick_dir']);
			} else {
				$this->error("imagemagick failed to edit the image. Returned with $code.");
			}
			print_r($output);
		}
		return $output;
	}

	public function create_color($hex, $alpha) {
		$red = hexdec(substr($hex, 1, 2));
		$green = hexdec(substr($hex, 3, 2));
		$blue = hexdec(substr($hex, 5, 2));
		return "\"rgba(" . $red . ", " . $green . ", " . $blue . ", " . round($alpha / 100, 2) . ")\"";
	}

	public function __destruct() {
		unlink($this->image_temp);
	}

}
