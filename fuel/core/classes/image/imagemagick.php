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

	protected function _load($return_data)
	{
		if (empty($this->image_temp))
		{
			do
			{
				$this->image_temp = substr($this->config['temp_dir'] . $this->config['temp_append'] . md5(time() * microtime()), 0, 32) . '.png';
			}
			while (file_exists($this->image_temp));
		}
		$this->exec('convert', '"' . $this->image_fullpath . '" "' . $this->image_temp . '"');
		$this->debug($this->image_fullpath . '<br />' . $this->image_temp);
	}

	protected function _crop($x1, $y1, $x2, $y2)
	{
		extract(parent::_crop($x1, $y1, $x2, $y2));
		$image = '"' . $this->image_temp . '"';
		$this->exec('convert', $image . ' -crop ' . ($x2 - $x1) . 'x' . ($y2 - $y1) . '+' . $x1 . '+' . $y1 . ' ' . $image);
	}

	protected function _resize($width, $height = null, $keepar = true, $pad = true)
	{
		extract(parent::_resize($width, $height, $keepar, $pad));
		$image = '"' . $this->image_temp . '"';
		$this->exec('convert', $image . " -resize " . $width . "x" . $height . ($keepar ? "!" : "") . " " . $image);
	}

	protected function _rotate($degrees)
	{
		extract(parent::_rotate($degrees));
		$color = $this->create_color($this->config['bgcolor'], 0);
		$image = '"' . $this->image_temp . '"';
		$this->exec('convert', $image . " -background " . $color . " -rotate " . $degrees . " " . $image);
	}

	protected function _watermark($filename, $x, $y)
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

	protected function _border($size, $color)
	{
		extract(parent::_border($size, $color));
		$image = '"' . $this->image_temp . '"';
		$command = $image . ' -compose copy -background none -bordercolor ' . $this->create_color($color, 100) . ' -border ' . $size . ' ' . $image;
		$this->exec('convert', $command);
	}

	protected function _mask($maskimage)
	{
		extract(parent::_mask($maskimage));
		$mimage = '"' . $maskimage . '"';
		$image = '"' . $this->image_temp . '"';
		$command = $image . ' ' . $mimage . ' +matte  -compose copy-opacity -composite ' . $image;
		$this->exec('convert', $command);
	}

	/**
	 * Credit to Leif Åstrand <leif@sitelogic.fi> for the rounded corners command
	 *
	 * @link	http://www.imagemagick.org/Usage/thumbnails/#rounded
	 * @param	<type>	$radius
	 * @param	<type>	$sides
	 * @param	<type>	$antialias
	 */
	protected function _rounded($radius, $sides, $antialias)
	{
		extract(parent::_rounded($radius, $sides, $antialias));
		$image = '"' . $this->image_temp . '"';
		$command = $image . ' ( ' .
				'+clone -alpha extract -draw ' .
				'"fill black polygon 0,0 0,' . $radius . ' ' . $radius . ',0 ' .
				'fill white circle ' . $radius . ',' . $radius . ' ' . $radius . ',0" ' .
				'( +clone -flip ) -compose Multiply -composite ' .
				'( +clone -flop ) -compose Multiply -composite ' .
				') -alpha off -compose CopyOpacity -composite ' . $image;
		$this->exec('convert', $command);
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

	public function save($filename, $permissions = null)
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
		$this->add_background();
		if (substr($this->image_fullpath, -1 * strlen($filetype)) != $filetype)
		{
			$old = '"' . $this->image_temp . '"';
			if (!$this->debugging)
				$this->exec('convert', $old . ' ' . strtolower($filetype) . ':-', true);
		}
		else
		{
			if (!$this->debugging)
				echo file_get_contents($this->image_temp);
		}
	}

	private function add_background()
	{
		if ($this->config['bgcolor'] != null)
		{
			$image = '"' . $this->image_temp . '"';
			$color = $this->create_color($this->config['bgcolor'], 100);
			$sizes = $this->sizes();
			$command = '-size ' . $sizes->width . 'x' . $sizes->height . ' ' . 'canvas:' . $color . ' ' .
					$image . ' -composite ' . $image;
			$this->exec('convert', $command);
		}
	}

	public function exec($program, $params, $passthru = false)
	{
		$command = realpath($this->config['imagemagick_dir'] . $program . ".exe") . " " . $params;
		$this->debug("Running command: <span style='font-family: courier;'>$command</span>");
		$code = 0;
		if (!$passthru)
			exec($command, $output, $code);
		else
			passthru($command);
		if ($code != 0)
		{
			// Try to come up with a common error?
			if (!file_exists(realpath($this->config['imagemagick_dir'] . $program . ".exe")))
			{
				$this->error("imagemagick executable not found in " . $this->config['imagemagick_dir']);
			}
			else
			{
				$this->error("imagemagick failed to edit the image. Returned with $code.");
			}
		}
		return $output;
	}

	protected function create_color($hex, $alpha)
	{
		if ($hex == null)
		{
			$red = 0;
			$green = 0;
			$blue = 0;
		}
		else
		{
			// Check if theres a # in front
			if (substr($hex, 0, 1) == '#')
				$hex = substr($hex, 1);
			// Break apart the hex
			if (strlen($hex) == 6) {
				$red = hexdec(substr($hex, 0, 2));
				$green = hexdec(substr($hex, 2, 2));
				$blue = hexdec(substr($hex, 4, 2));
			} else {
				$red = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
				$green = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
				$blue = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
			}
		}
		return "\"rgba(" . $red . ", " . $green . ", " . $blue . ", " . round($alpha / 100, 2) . ")'\"";
	}

	public function __destruct()
	{
		unlink($this->image_temp);
	}

}
