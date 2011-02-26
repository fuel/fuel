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

	public function init($index, $value = null)
	{
		if (is_array($index))
		{
			$this->config = array_merge($this->config, $index);
		} else
		{
			$this->_config($i, $v);
		}
	}

	public function _config($index, $value)
	{
		$this->config[$index] = $value;
	}

	/**
	 * Exectues the presets set in the config. Additional parameters replace the $1, $2, ect.
	 * 
	 * @param <type> $name
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
			} else
			{
				$this->error("The library does not support this filetype for <i>$filename</i>.");
			}
		} else
		{
			$this->error("Image file <i>$filename</i> does not exist.");
		}
		return $return_data ? $return : $this;
	}

	abstract public function _load($return);

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
	}

	public function _crop($x1, $y1 = null, $x2 = null, $y2 = null)
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

	public function _resize($width, $height = null, $keepar = true, $pad = true)
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
	}

	public function _rotate($degrees)
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

	public function _watermark($filename, $position, $padding = 5)
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

	public function border($size, $color = null)
	{
		$this->queue('border', $size, $color);
	}

	public function _border($size, $color = null)
	{
		if (empty($color))
			$color = $this->config['bgcolor'];
		return array(
			'size' => $size,
			'color' => $color
		);
	}

	public function mask($maskimage)
	{
		$this->queue('mask', $maskimage);
		return $this;
	}

	public function _mask($maskimage)
	{

		return array(
			'maskimage' => $maskimage
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
		return static::instance()->save($filename, $permissions);
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
		}
		return array(
			'filetype' => $filetype
		);
	}

	abstract public function sizes($filename = null);

	/**
	 * Checks if the extension is accepted by this library, and if its valid sets the $this->image_extension variable.
	 *
	 * @param	string	$filename
	 * @param	boolean	$writevar	Decides if the extension should be write to $this->image_extension
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
		// Make sure its within bounds
		if ($input > $size)
			$input = $size;
		if ($input < -$size)
			$input = -$size;
		// Negatives are based off the bottom right
		if ($x !== null && $input < 0)
		{
			$input = $size + $input;
		}
		$this->debug("convert_number($orig, $x) => $input");
		return $input;
	}

	protected function queue($function)
	{
		$func = func_get_args();
		$this->debug("Queued " . implode(", ", $func) . "");
		$this->queued_actions[] = $func;
	}

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

	protected function debug($message)
	{
		if ($this->debugging)
		{
			echo '<div>' . $message . '</div>';
		}
	}

	protected function error($message)
	{
		throw new \Fuel_Exception($message);
	}

}
