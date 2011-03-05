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

abstract class Image_Driver {

	protected $debugging = false;
	protected $image_fullpath = null;
	protected $image_driectory = null;
	protected $image_filename = null;
	protected $image_extension = null;
	protected $config = array();
	protected $queued_actions = array();
	protected $accepted_extension;

	public function __construct($config)
	{
		$this->init($config);
	}

	/**
	 * Sets the configuration options for the class, using an array.
	 *
	 * @param	array	$options
	 */
	public function init($options)
	{
		if (is_array($options))
		{
			$this->config = array_merge($this->config, $options);
		}
	}

	/**
	 *
	 * @param	string	$index	The option to set.
	 * @param	mixed	$value	The value to set the option to.
	 */
	public function config($index, $value)
	{
		$this->config[$index] = $value;
		return $this;
	}

	/**
	 * Exectues the presets set in the config. Additional parameters replace the $1, $2, ect.
	 * 
	 * @param	string	$name	The name of the preset.
	 */
	public function preset($name)
	{
		$vars = func_get_args();
		if (isset($this->config['presets'][$name]))
		{
			$old_config = $this->config;
			$this->config = array_merge($this->config, $this->config['presets'][$name]);
			foreach ($this->config['actions'] AS $action)
			{
				for ($i = 1; $i < count($action); $i++)
				{
					for ($x = 0; $x < count($vars); $x++)
					{
						$action[$i] = $string = preg_replace('#\$' . ($x + 1) . '#', $vars[$x], $action[$i]);
					}
				}
				$vars = array_slice($action, 1);
				call_user_func_array(array(&$this, $action[0]), $vars);
			}
			$this->config = $old_config;
		}
	}

	/**
	 * Loads the image and checks if its compatable.
	 *
	 * @param	string	$filename	The file to load
	 * @param	string	$return_data	Decides if it should return the images data, or just "$this".
	 * @return	Image_Driver
	 */
	public function load($filename, $return_data = false)
	{
		// First check if the filename exists
		$filename = realpath($filename);
		$return = null;
		if (file_exists($filename))
		{
			// Set the directory, filename, and extension.
			$this->image_fullpath = $filename;
			$this->image_directory = dirname($filename);
			$this->image_filename = basename($filename);
			if ($this->check_extension($filename) !== false)
			{
				$return = $this->_load($return_data);
			}
			else
			{
				$this->error("The library does not support this filetype for <i>$filename</i>.");
			}
		}
		else
		{
			$this->error("Image file <i>$filename</i> does not exist.");
		}
		return $return_data ? $return : $this;
	}

	abstract protected function _load($return);

	/**
	 * Crops the image using coordinates or percentages.
	 *
	 * Positive whole numbers or percentages are coordinates from the top left.
	 *
	 * Negative whole numbers or percentages are coordinates from the bottom right.
	 *
	 * @param	integer	$x1	X-Coordinate for first set.
	 * @param	integer	$y1	Y-Coordinate for first set.
	 * @param	integer	$x2	X-Coordinate for second set.
	 * @param	integer	$y2	Y-Coordinate for second set.
	 * @return	Image_Driver
	 */
	public function crop($x1, $y1 = null, $x2 = null, $y2 = null)
	{
		$this->queue('crop', $x1, $y1, $x2, $y2);
		return $this;
	}

	protected function _crop($x1, $y1 = null, $x2 = null, $y2 = null)
	{
		if ($y1 === null)
			$y1 = $x1;
		if ($x2 === null)
			$x2 = "-" . $x1;
		if ($y2 === null)
			$y2 = "-" . $y1;

		$x1 = $this->convert_number($x1, true);
		$y1 = $this->convert_number($y1, false);
		$x2 = $this->convert_number($x2, true);
		$y2 = $this->convert_number($y2, false);

		return array(
			'x1' => $x1,
			'y1' => $y1,
			'x2' => $x2,
			'y2' => $y2
		);
	}

	/**
	 * Resizes the image. If the width or height is null, it will resize retaining the original aspect ratio.
	 *
	 * @param	integer	$width	The new width of the image.
	 * @param	integer	$height	The new height of the image.
	 * @param	boolean	$keepar	If false, allows stretching of the image.
	 * @param	boolean	$pad	Adds padding to the image when resizing.
	 * @return	Image_Driver
	 */
	public function resize($width, $height = null, $keepar = true, $pad = true)
	{
		$this->queue('resize', $width, $height, $keepar, $pad);
		return $this;
	}

	protected function _resize($width, $height = null, $keepar = true, $pad = true)
	{
		if (empty($height))
			$height = $width;
		return array(
			'width' => $width,
			'height' => $height,
			'keepar' => $keepar,
			'pad' => $pad
		);
	}

	/**
	 * Rotates the image
	 *
	 * @param	integer	$degrees	The degrees to rotate, negatives integers allowed.
	 * @param	Image_Driver
	 */
	public function rotate($degrees)
	{
		$this->queue('rotate', $degrees);
		return $this;
	}

	protected function _rotate($degrees)
	{
		$degrees %= 360;
		if ($degrees < 0)
		{
			$degrees = 360 + $degrees;
		}
		$this->debug("Image being rotated $degrees");
		return array(
			'degrees' => $degrees
		);
	}

	/**
	 * Adds a watermark to the image.
	 *
	 * @param	string	$filename	The filename of the watermark file to use.
	 * @param	string	$position	The position of the watermark, ex: "bottom right", "center center", "top left"
	 * @param	integer	$padding	The amount of padding (in pixels) from the position.
	 * @param	Image_Driver
	 */
	public function watermark($filename, $position, $padding = 5)
	{
		$this->queue('watermark', $filename, $position, $padding);
		return $this;
	}

	protected function _watermark($filename, $position, $padding = 5)
	{
		$filename = realpath($filename);
		$return = false;
		if (file_exists($filename) && $this->check_extension($filename, false))
		{
			$x = 0;
			$y = 0;
			$wsizes = $this->sizes($filename);
			$sizes = $this->sizes();
			// Get the x and y  positions.
			list($ypos, $xpos) = explode(' ', $position);
			switch ($xpos)
			{
				case 'left':
					$x = $padding;
					break;
				case 'middle':
				case 'center':
					$x = ($sizes->width / 2) - ($wsizes->width / 2);
					break;
				case 'right':
					$x = $sizes->width - $wsizes->width - $padding;
					break;
			}
			switch ($ypos)
			{
				case 'top':
					$y = $padding;
					break;
				case 'middle':
				case 'center':
					$y = ($sizes->height / 2) - ($wsizes->height / 2);
					break;
				case 'bottom':
					$y = $sizes->height - $wsizes->height - $padding;
					break;
			}
			$return = array(
				'filename' => $filename,
				'x' => $x,
				'y' => $y
			);
		}
		return $return;
	}

	/**
	 * Adds a border to the image.
	 *
	 * @param	integer	$size	The side of the border, in pixels.
	 * @param	string	$color	A hexidecimal color.
	 * @param	Image_Driver
	 */
	public function border($size, $color = null)
	{
		$this->queue('border', $size, $color);
		return $this;
	}

	protected function _border($size, $color = null)
	{
		if (empty($color))
			$color = $this->config['bgcolor'];
		return array(
			'size' => $size,
			'color' => $color
		);
	}

	/**
	 * Masks the image using the alpha channel of the image input.
	 *
	 * @param	string	$maskimage	The location of the image to use as the mask
	 * @return	Image_Driver
	 */
	public function mask($maskimage)
	{
		$this->queue('mask', $maskimage);
		return $this;
	}

	protected function _mask($maskimage)
	{

		return array(
			'maskimage' => $maskimage
		);
	}

	/**
	 * Adds rounded corners to the image.
	 *
	 * @param	integer	$radius
	 * @param	integer	$sides	Accepts any combination of "tl tr bl br" seperated by spaces, or null for all sides
	 * @param	integer	$antialias	Sets the antialias range.
	 * @return	Image_Driver
	 */
	public function rounded($radius, $sides = null, $antialias = null)
	{
		$this->queue('rounded', $radius, $sides, $antialias);
		return $this;
	}

	protected function _rounded($radius, $sides, $antialias)
	{
		$tl = $tr = $bl = $br = $sides == null;
		if ($sides != null)
		{
			$sides = explode(" ", $sides);
			foreach ($sides AS $side)
			{
				if ($side == 'tl' || $side == 'tr' || $side == 'bl' || $side == 'br')
					$$side = true;
			}
		}
		if ($antialias == null)
			$antialias = $this->config['antialias'];
		return Array(
			'radius' => $radius,
			'tl' => $tl,
			'tr' => $tr,
			'bl' => $bl,
			'br' => $br,
			'antialias' => $antialias
		);
	}

	/**
	 * Saves the image, and optionally attempts to set permissions
	 *
	 * @param	string	$filename	The location where to save the image.
	 * @param	string	$permissions	Allows unix style permissions
	 */
	public function save($filename, $permissions = null)
	{
		$directory = dirname($filename);
		if (!is_dir($directory))
			$this->error("Could not find directory \"$directory\"");
		// Touch the file
		if (!touch($filename))
			$this->error("Do not have permission to write to \"$filename\"");
		// Set the new permissions
		if ($permissions != null)
			if (!chmod($filename, $permissions))
				$this->error("Could not set permissions on the file.");
		if (!$this->check_extension($filename, true))
			$filename .= "." . $this->image_extension;
		return Array(
			'filename' => $filename
		);
	}

	/**
	 * Outputs the file directly to the user.
	 *
	 * @param	string	$filetype	The extension type to use. Ex: png, jpg, gif
	 */
	public function output($filetype = null)
	{
		if ($filetype == null)
			if ($this->config['filetype'] == null)
				$filetype = $this->image_extension;
			else
				$filetype = $this->config['filetype'];
		if ($this->check_extension($filetype, false))
		{
			if (!$this->debugging)
				header('Content-Type: image/' . $filetype);
		} else
		{
			$this->error("Image extension $filetype is unsupported.");
		}
		return array(
			'filetype' => $filetype
		);
	}

	/**
	 * Returns sizes for the currently loaded image, or the image given in the $filename.
	 *
	 * @param	string	$filename	The location of the file to get sizes for.
	 * @return	object	An object containing width and height variables.
	 */
	abstract public function sizes($filename = null);

	/**
	 * Checks if the extension is accepted by this library, and if its valid sets the $this->image_extension variable.
	 *
	 * @param	string	$filename
	 * @param	boolean	$writevar	Decides if the extension should be written to $this->image_extension
	 * @return	boolean
	 */
	protected function check_extension($filename, $writevar = true)
	{
		$return = false;
		foreach ($this->accepted_extensions AS $ext)
		{
			if (substr($filename, strlen($ext) * -1) == $ext)
			{
				if ($writevar)
					$this->image_extension = $ext;
				$return = true;
			}
		}
		return $return;
	}

	/**
	 * Converts percentages, negatives, and other values to absolute integers.
	 *
	 * @param	string	$input
	 * @param	boolean	$x	Determines if the number relates to the x-axis or y-axis.
	 * @return	integer	The converted number, useable with the image being edited.
	 */
	protected function convert_number($input, $x = null)
	{
		// Sanatize double negatives
		$input = str_replace('--', '', $input);

		$orig = $input;
		$sizes = $this->sizes();
		$size = $x ? $sizes->width : $sizes->height;
		// Convert percentages to absolutes
		if (substr($input, -1) == '%')
		{
			$input = floor((substr($input, 0, -1) / 100) * $size);
		}
		// Negatives are based off the bottom right
		if ($x !== null && $input < 0)
		{
			$input = $size + $input;
		}
		$this->debug("convert_number($orig, $x) => $input");
		return $input;
	}

	/**
	 * Queues a function to run at a later time.
	 *
	 * @param	string	$function	The name of the function to be ran, without the leading _
	 */
	protected function queue($function)
	{
		$func = func_get_args();
		$this->debug("Queued " . implode(", ", $func) . "");
		$this->queued_actions[] = $func;
	}

	/**
	 * Runs all queued actions on the loaded image.
	 *
	 * @param	boolean	$clear	Decides if the queue should be cleared once completed.
	 */
	public function run_queue($clear = true)
	{
		foreach ($this->queued_actions AS $rawaction)
		{
			$action = $rawaction;
			$this->debug("Executing " . implode(", ", $action) . "");
			call_user_func_array(array(&$this, '_' . $action[0]), array_slice($action, 1));
		}
		if ((bool) $clear)
			$this->queued_actions = array();
	}

	/**
	 * Used for debugging image output.
	 *
	 * @param	string	$message
	 */
	protected function debug($message)
	{
		if ($this->debugging)
		{
			echo '<div>' . $message . '</div>';
		}
	}

	/**
	 * Handles errors for the image class.
	 *
	 * @param	string	$message	The message to send along with the error.
	 */
	protected function error($message)
	{
		throw new \Fuel_Exception($message);
	}

}
