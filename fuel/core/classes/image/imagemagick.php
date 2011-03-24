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
 * @license		MIT License
 * @copyright	2010 - 2011 Fuel Development Team
 * @link		http://fuelphp.com
 */

namespace Fuel\Core;

class Image_Imagemagick extends Image_Driver {

	private $image_temp = null;
	protected $accepted_extensions = array('png', 'gif', 'jpg', 'jpeg');
	private $size_cache = null;

	public function load($filename)
	{
		extract(parent::load($filename));

		$this->clear_sizes();
		if (empty($this->image_temp))
		{
			do
			{
				$this->image_temp = substr($this->config['temp_dir'].$this->config['temp_append'].md5(time() * microtime()), 0, 32).'.png';
			}
			while (file_exists($this->image_temp));
		}
		$this->exec('convert', '"'.$image_fullpath.'" "'.$this->image_temp.'"');

		return $this;
	}

	protected function _crop($x1, $y1, $x2, $y2)
	{
		extract(parent::_crop($x1, $y1, $x2, $y2));
		$image = '"'.$this->image_temp.'"';
		$this->exec('convert', $image.' -crop '.($x2 - $x1).'x'.($y2 - $y1).'+'.$x1.'+'.$y1.' +repage '.$image);
		$this->clear_sizes();
	}

	protected function _resize($width, $height = null, $keepar = true, $pad = true)
	{
		extract(parent::_resize($width, $height, $keepar, $pad));

		$image = '"'.$this->image_temp.'"';
		$this->exec('convert', "-define png:size=".$cwidth."x".$cheight." ".$image." ".
				"-background none ".
				"-resize \"".($pad ? $width : $cwidth)."x".($pad ? $height : $cheight)."!\" ".
				"-gravity center ".
				"-extent ".$cwidth."x".$cheight." ".$image);
		$this->clear_sizes();
	}

	protected function _rotate($degrees)
	{
		extract(parent::_rotate($degrees));

		$image = '"'.$this->image_temp.'"';
		$this->exec('convert', $image." -background none -virtual-pixel background +distort ScaleRotateTranslate ".$degrees." +repage ".$image);

		$this->clear_sizes();
	}

	protected function _watermark($filename, $x, $y)
	{
		extract(parent::_watermark($filename, $x, $y));

		$image = '"'.$this->image_temp.'"';
		$filename = '"'.$filename.'"';
		$x >= 0 and $x = '+'.$x;
		$y >= 0 and $y = '+'.$y;

		$this->exec(
			'composite',
			'-compose atop -geometry '.$x.$y.' '.
			'-dissolve '.$this->config['watermark_alpha'].'% '.
			$filename.' '.$image.' '.$image
		);
	}

	protected function _border($size, $color)
	{
		extract(parent::_border($size, $color));

		$image = '"'.$this->image_temp.'"';
		$color = $this->create_color($color, 100);
		$command = $image.' -compose copy -bordercolor '.$color.' -border '.$size.'x'.$size.' '.$image;
		$this->exec('convert', $command);

		$this->clear_sizes();
	}

	protected function _mask($maskimage)
	{
		extract(parent::_mask($maskimage));

		$mimage = '"'.$maskimage.'"';
		$image = '"'.$this->image_temp.'"';
		$command = $image.' '.$mimage.' +matte  -compose copy-opacity -composite '.$image;
		$this->exec('convert', $command);
	}

	/**
	 * Credit to Leif Åstrand <leif@sitelogic.fi> for the base of the round corners.
	 *
	 * Note there is a defect with this, as non-transparent corners get opaque circles of color. Maybe mask it with auto-generated corners?
	 *
	 * @link  http://www.imagemagick.org/Usage/thumbnails/#rounded
	 */
	protected function _rounded($radius, $sides)
	{
		extract(parent::_rounded($radius, $sides, null));

		$image = '"'.$this->image_temp.'"';
		$r = $radius;
		$command = $image." ( +clone -alpha extract ".
			( ! $tr ? '' : "-draw \"fill black polygon 0,0 0,$r $r,0 fill white circle $r,$r $r,0\" ")."-flip ".
			( ! $br ? '' : "-draw \"fill black polygon 0,0 0,$r $r,0 fill white circle $r,$r $r,0\" ")."-flop ".
			( ! $bl ? '' : "-draw \"fill black polygon 0,0 0,$r $r,0 fill white circle $r,$r $r,0\" ")."-flip ".
			( ! $tl ? '' : "-draw \"fill black polygon 0,0 0,$r $r,0 fill white circle $r,$r $r,0\" ").
			') -alpha off -compose CopyOpacity -composite '.$image;
		$this->exec('convert', $command);
	}

	public function sizes($filename = null, $usecache = true)
	{
		$is_loaded_file = $filename == null;
		if ( ! $is_loaded_file or $this->sizes_cache == null or !$usecache)
		{
			$reason = ($filename != null ? "filename" : ($this->size_cache == null ? 'cache' : 'option'));
			$this->debug("Generating size of image... (triggered by $reason)");

			if ($is_loaded_file and ! empty($this->image_temp))
			{
				$filename = $this->image_temp;
			}

			$output = $this->exec('identify', '-format "%[fx:w] %[fx:h]" "'.$filename.'"');
			list($width, $height) = explode(" ", $output[0]);
			$return = (object) array(
				'width' => $width,
				'height' => $height
			);

			if ($is_loaded_file)
			{
				$this->sizes_cache = $return;
			}
			$this->debug("Sizes ".( !$is_loaded_file ? "for <code>$filename</code> " : "")."are now $width and $height");
		}
		else
		{
			$return = $this->sizes_cache;
		}
		return $return;
	}

	public function save($filename, $permissions = null)
	{
		extract(parent::output($filename, $permissions));

		$this->run_queue();
		$old = '"'.$this->image_temp.'"';
		$new = '"'.$filename.'"';
		$this->exec('convert', $old.' '.$new);

		return $this;
	}

	public function output($filetype = null)
	{
		extract(parent::output($filetype));

		$this->run_queue();
		$this->add_background();

		if (substr($this->image_fullpath, -1 * strlen($filetype)) != $filetype)
		{
			$old = '"'.$this->image_temp.'"';
			if ( ! $this->config['debug'])
			{
				$this->exec('convert', $old.' '.strtolower($filetype).':-', true);
			}
		}
		else
		{
			if ( ! $this->config['debug'])
			{
				echo file_get_contents($this->image_temp);
			}
		}
		return $this;
	}

	/**
	 * Cleared the currently loaded sizes, used to removed cached sizes.
	 */
	protected function clear_sizes()
	{
		$this->sizes_cache = null;
	}

	protected function add_background()
	{
		if ($this->config['bgcolor'] != null)
		{
			$image   = '"'.$this->image_temp.'"';
			$color   = $this->create_color($this->config['bgcolor'], 100);
			$sizes   = $this->sizes();
			$command = '-size '.$sizes->width.'x'.$sizes->height.' '.'canvas:'.$color.' '.
				$image.' -composite '.$image;
			$this->exec('convert', $command);
		}
	}

	/**
	 * Executes the specified imagemagick executable and returns the output.
	 *
	 * @param  string   $program   The name of the executable.
	 * @param  string   $params    The parameters of the executable.
	 * @param  boolean  $passthru  Returns the output if false or pass it to browser.
	 * @return mixed    Either returns the output or returns nothing.
	 */
	private function exec($program, $params, $passthru = false)
	{
		$command = realpath($this->config['imagemagick_dir'].$program.".exe")." ".$params;
		$this->debug("Running command: <code>$command</code>");
		$code = 0;
		$output = null;

		$passthru ? passthru($command) : exec($command, $output, $code);

		if ($code != 0)
		{
			// Try to come up with a common error?
			if ( ! file_exists(realpath($this->config['imagemagick_dir'].$program.".exe")))
			{
				throw new \Fuel_Exception("imagemagick executable not found in ".$this->config['imagemagick_dir']);
			}
			else
			{
				throw new \Fuel_Exception("imagemagick failed to edit the image. Returned with $code.");
			}
		}
		return $output;
	}

	/**
	 * Creates a new color usable by ImageMagick.
	 *
	 * @param  string   $hex    The hex code of the color
	 * @param  integer  $alpha  The alpha of the color, 0 (trans) to 100 (opaque)
	 * @return string   rgba representation of the hex and alpha values.
	 */
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
			{
				$hex = substr($hex, 1);
			}

			// Break apart the hex
			if (strlen($hex) == 6)
			{
				$red   = hexdec(substr($hex, 0, 2));
				$green = hexdec(substr($hex, 2, 2));
				$blue  = hexdec(substr($hex, 4, 2));
			}
			else
			{
				$red   = hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
				$green = hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
				$blue  = hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
			}
		}
		return "\"rgba(".$red.", ".$green.", ".$blue.", ".round($alpha / 100, 2).")\"";
	}

	public function __destruct()
	{
		unlink($this->image_temp);
	}
}

// End of file imagemagic.php