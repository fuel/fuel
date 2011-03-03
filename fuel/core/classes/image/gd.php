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

class Image_Gd extends Image_Driver {

	private $image_data = null;
	protected $accepted_extensions = array('png', 'gif', 'jpg', 'jpeg');
	protected $gdresizefunc = "imagecopyresampled";

	public function _load($return_data)
	{
		$return = false;
		$extension = $this->image_extension;
		if ($extension == 'jpg')
			$extension = 'jpeg';
		// Check if the function exists
		if (function_exists('imagecreatefrom' . $extension))
		{
			// Create a new transparent image.
			$sizes = $this->sizes($this->image_fullpath);
			$tmpImage = call_user_func('imagecreatefrom' . $extension, $this->image_fullpath);
			$image = $this->create_transparent_image($sizes->width, $sizes->height, $tmpImage);
			if (!$return_data)
			{
				$this->debug("load(): Override existing image.");
				$this->image_data = $image;
				$return = true;
			} else
			{
				$this->debug("load(): Returning image.");
				$return = $image;
			}
		} else
		{
			throw new \Fuel_Exception("Function imagecreatefrom" . $extension . "() does not exist (Missing GD?)");
		}
		return $return;
	}

	public function _crop($x1, $y1, $x2, $y2)
	{
		extract(parent::_crop($x1, $y1, $x2, $y2));
		$width = $x2 - $x1;
		$height = $y2 - $y1;
		$image = $this->create_transparent_image($width, $height);
		call_user_func($this->gdresizefunc, $image, $this->image_data, 0, 0, $x1, $y1, $width, $height, $width, $height);
		$this->image_data = $image;
	}

	public function _resize($width, $height = null, $keepar = true, $pad = true)
	{
		extract(parent::_resize($width, $height, $keepar, $pad));
		$origwidth = $this->convert_number($width);
		$origheight = $this->convert_number($height);
		$width = $origwidth;
		$height = $origheight;
		if ($keepar)
		{
			$sizes = $this->sizes();
			// See which is the biggest ratio
			$width_ratio = $width / $sizes->width;
			$height_ratio = $height / $sizes->height;
			if ($width_ratio > $height_ratio)
			{
				$width = floor($sizes->width * $height_ratio);
			} else
			{
				$height = floor($sizes->height * $width_ratio);
			}
		}
		$x = 0;
		$y = 0;
		if ($pad)
		{
			$x = floor(($origwidth - $width) / 2);
			$y = floor(($origheight - $height) / 2);
		} else
		{
			$origwidth = $width;
			$origheight = $height;
		}
		$sizes = $this->sizes();
		// Add the original image.
		$image = $this->create_transparent_image($origwidth, $origheight);
		call_user_func($this->gdresizefunc, $image, $this->image_data, $x, $y, 0, 0, $width, $height, $sizes->width, $sizes->height);
		$this->image_data = $image;
	}

	public function _rotate($degrees)
	{
		extract(parent::_rotate($degrees));
		$degrees = 360 - $degrees;
		$color = $this->create_color($this->config['bgcolor'], 0);
		$this->image_data = imagerotate($this->image_data, $degrees, $color, 0);
	}

	public function _watermark($filename, $x, $y)
	{
		$values = parent::_watermark($filename, $x, $y);
		if ($values == false)
		{
			$this->error("Watermark image not found or invalid filetype.");
		} else
		{
			extract($values);
			$wsizes = $this->sizes($filename);
			$sizes = $this->sizes();

			$image = $this->create_transparent_image($sizes->width, $sizes->height, $this->image_data);
			$watermark = $this->create_transparent_image($wsizes->width, $wsizes->height, $this->load($filename, true));
			// Used as a workaround for lack of alpha support in imagecopymerge.

			$this->image_merge($image, $watermark, $x, $y, $this->config['watermark_alpha']);

			$this->image_data = $image;
		}
	}

	public function _border($size, $color)
	{
		extract(parent::_border($size, $color));
		$color = $this->create_color($color, 127);
		$sizes = $this->sizes();
		$image = $this->create_transparent_image($sizes->width + ($size * 2), $sizes->height + ($size * 2));
		imagefilledrectangle($image, 0, 0, $size, $sizes->height, $color);
		imagefilledrectangle($image, 0, 0, $sizes->width, $size, $color);
		imagefilledrectangle($image, $sizes->width - $size, 0, $sizes->width, $sizes->height, $color);
		imagefilledrectangle($image, 0, $sizes->height - $size, $sizes->height, $sizes->width, $color);
		$this->image_merge($image, $this->image_data, $size, $size, 127);
		$this->image_data = $image;
	}

	public function _mask($maskimage)
	{
		extract(parent::_mask($maskimage));
		// Get size and width of image
		$sizes = $this->sizes();
		$masksizes = $this->sizes($maskimage);
		// Create new blank image
		$image = $this->create_transparent_image($sizes->width, $sizes->height);
		if (is_resource($maskimage))
		{
			$maskim = $maskimage;
		} else
		{
			$maskim = $this->load($maskimage, true);
		}
		// Loop through all the pixels
		for ($x = 0; $x < $masksizes->width; $x++)
		{
			for ($y = 0; $y < $masksizes->height; $y++)
			{
				$maskcolor = imagecolorat($maskim, $x, $y);
				$maskcolor = imagecolorsforindex($maskim, $maskcolor);
				$maskalpha = $maskcolor['alpha'];
				$ourcolor = imagecolorat($this->image_data, $x, $y);
				$ourcolor = imagecolorsforindex($this->image_data, $ourcolor);
				$ouralpha = 127 - (($ourcolor & 0x7f000000) >> 24);
				$newalpha = floor($ouralpha - (($maskalpha / 127) * $ouralpha));
				$newcolor = imagecolorallocatealpha($image, $ourcolor['red'], $ourcolor['green'], $ourcolor['blue'], 127 - $newalpha);
				imagesetpixel($image, $x, $y, $newcolor);
			}
		}
		$this->image_data = $image;
	}

	public function _rounded($radius, $tl)
	{
		$this->round_corner($this->image_data, $radius, true, false);
		$this->round_corner($this->image_data, $radius, true, true);
		$this->round_corner($this->image_data, $radius, false, true);
		$this->round_corner($this->image_data, $radius, false, false);
	}

	private function round_corner(&$image, $radius, $top, $left)
	{
		$sX = $left ? -$radius : 0;
		$sY = $top ? -$radius : 0;
		$eX = $left ? 0 : $radius;
		$eY = $top ? 0 : $radius;
		// Get this images size
		$sizes = $this->sizes();
		$offsetX = ($left ? $radius : $sizes->width - $radius - 1 );
		$offsetY = ($top ? $radius : $sizes->height - $radius - 2);
		$antialias = 1;
		// Set the images alpha blend to false
		imagealphablending($image, false);
		// Make this color ahead time
		$transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
		for ($x = $sX; $x <= $eX; $x++)
		{
			for ($y = $sY; $y <= $eY; $y++)
			{
				$dist = sqrt(($x * $x) + ($y * $y));
				if ($dist <= $radius + $antialias)
				{
					// Decide if anything needs to be changed
					// We subtract from antialias so the transparency makes sense.
					$fromCirc = $dist - $radius;
					if ($fromCirc > 0)
					{
						if ($fromCirc == 0)
						{
							imagesetpixel($image, $x + $offsetX, $y + $offsetY, $transparent);
						} else
						{
							// Get color information from this spot on the image
							$rgba = imagecolorat($image, $x + $offsetX, $y + $offsetY);
							$tmpColor = imagecolorallocatealpha(
											$image,
											($rgba >> 16) & 0xFF, // Red
											($rgba >> 8) & 0xFF, // Green
											$rgba & 0xFF, // Blue
											(127 - (($rgba >> 24) & 0xFF)) * ($fromCirc / $antialias) // Alpha
							);
							imagesetpixel($image, $x + $offsetX, $y + $offsetY, $tmpColor);
						}
					}
				} else
				{
					// Clear this area out...
					imagesetpixel($image, $x + $offsetX, $y + $offsetY, $transparent);
				}
			}
		}
		// Reset alpha blending
		imagealphablending($image, false);
	}

	public function sizes($filename = null)
	{
		if (empty($filename) && !empty($this->image_fullpath))
			$filename = $this->image_fullpath;
		$width = null;
		$height = null;
		if ($filename == $this->image_fullpath && is_resource($this->image_data))
		{
			$width = imagesx($this->image_data);
			$height = imagesy($this->image_data);
			$this->debug("Sizes returned $width and $height from image_data resource.");
		} else if (is_resource($filename))
		{
			$width = imagesx($filename);
			$height = imagesy($filename);
			$this->debug("Sizes returned $width and $height from resource.");
		} else
		{
			list($width, $height) = getimagesize($filename);
			$this->debug("Sizes returned $width and $height from file.");
		}
		return (object) array('width' => $width, 'height' => $height);
	}

	public function _save($filename, $permissions = null)
	{
		call_user_func('image' . $this->image_extension, $this->image_data, $filename, $this->config['quality']);
	}

	public function output($filetype = null)
	{
		if ($filetype == 'gif')
			$this->gdresizefunc = 'imagecopyresized';
		else
			$this->gdresizefunc = 'imagecopyresampled';

		extract(parent::output($filetype));

		$this->run_queue();

		$sizes = $this->sizes();
		//$image = $this->create_transparent_image($sizes->width, $sizes->height, $this->image_data, $this->create_color($this->config['bgcolor'], $this->config['bgalpha']));
		$vars = array($this->image_data, null);
		if ($filetype == 'jpg' || $filetype == 'jpeg')
			$vars[] = $this->config['quality'];
		if (!$this->debugging)
			call_user_func_array('image' . $filetype, $vars);
	}

	/**
	 *
	 * @param <type> $hex
	 * @param <type> $alpha
	 * @param <type> $ext
	 * @return <type>
	 */
	private function create_color($hex, $alpha)
	{
		if (!is_resource($this->image_data))
			$this->image_data = imagecreatetruecolor(1, 1);
		// Break apart the hex
		$red = hexdec(substr($hex, 1, 2));
		$green = hexdec(substr($hex, 3, 2));
		$blue = hexdec(substr($hex, 5, 2));
		// Check if the transparency is allowed
		return imagecolorallocatealpha($this->image_data, $red, $green, $blue, 127 - floor($alpha * 1.27));
	}

	/**
	 *
	 * @param	integer	The width of the image.
	 * @param	integer	The height of the image.
	 * @param	resource	Optionally add an image to the new transparent image.
	 * @return	resource	Returns the image in resource form.
	 */
	private function create_transparent_image($width, $height, $resource = null, $color = null)
	{
		if ($color == null)
		{
			$this->debug("Color was null, resource is " . (is_resource($resource) ? "a resource!" : "nothin."));
			$color = $this->create_color('#FFFFFF', 0);
		} else
		{
			$this->debug("Color was not null, resource is " . (is_resource($resource) ? "a resource!" : "nothin."));
		}
		$image = imagecreatetruecolor($width, $height);
		imagesavealpha($image, true);
		imagecolortransparent($image, $color);
		// Set the blending mode to false, add the bgcolor, then switch it back.
		imagealphablending($image, false);
		imagefilledrectangle($image, 0, 0, $width, $height, $color);
		imagealphablending($image, true);
		if (is_resource($resource))
			imagecopyresized($image, $resource, 0, 0, 0, 0, $width, $height, $width, $height);
		return $image;
	}

	private function image_merge(&$image, $watermark, $x, $y, $alpha)
	{
		$wsizes = $this->sizes($watermark);
		$tmpimage = $this->create_transparent_image($wsizes->width, $wsizes->height);
		imagecopy($tmpimage, $image, 0, 0, $x, $y, $wsizes->width, $wsizes->height);
		imagecopy($tmpimage, $watermark, 0, 0, 0, 0, $wsizes->width, $wsizes->height);
		imagealphablending($image, false);
		imagecopymerge($image, $tmpimage, $x, $y, 0, 0, $wsizes->width, $wsizes->height, $alpha);
		imagealphablending($image, true);
	}

}
