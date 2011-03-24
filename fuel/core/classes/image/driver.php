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

abstract class Image_Driver {

	protected $image_fullpath  = null;
	protected $image_directory = null;
	protected $image_filename  = null;
	protected $image_extension = null;
	protected $config          = array();
	protected $queued_actions  = array();
	protected $accepted_extension;

	public function __construct($config)
	{
		$this->config = Config::load('image');
		$this->config($config);
	}
	/**
	 * Accepts configuration in either an array (as $index) or a pairing using $index and $value
	 *
	 * @param  string  $index  The index to be set, or an array of configuration options.
	 * @param  mixed   $value  The value to be set if $index is not an array.
	 */
	public function config($index = null, $value = null)
	{
		if (is_array($index))
		{
			$this->config = array_merge($this->config, $index);
		}
		elseif ($index != null)
		{
			$this->config[$index] = $value;
		}
		return $this;
	}

	/**
	 * Exectues the presets set in the config. Additional parameters replace the $1, $2, ect.
	 *
	 * @param  string  $name  The name of the preset.
	 */
	public function preset($name)
	{
		$vars = func_get_args();
		if (isset($this->config['presets'][$name]))
		{
			$old_config   = $this->config;
			$this->config = array_merge($this->config, $this->config['presets'][$name]);
			foreach ($this->config['actions'] AS $action)
			{
				$func = $action[0];
				array_shift($action);
				for ($i = 0; $i < count($action); $i++)
				{
					for ($x = count($vars) - 1; $x >= 0; $x--)
					{
						$action[$i] = preg_replace('#\$' . $x . '#', $vars[$x], $action[$i]);
					}
				}
				call_user_func_array(array($this, $func), $action);
			}
			$this->config = $old_config;
		}
		else
		{
			throw new \Fuel_Exception("Could not load preset $name, you sure it exists?");
		}
	}

	/**
	 * Loads the image and checks if its compatable.
	 *
	 * @param   string  $filename     The file to load
	 * @param   string  $return_data  Decides if it should return the images data, or just "$this".
	 * @return  Image_Driver
	 */
	public function load($filename, $return_data = false)
	{
		// First check if the filename exists
		$filename = realpath($filename);
		$return = array(
			'filename'    => $filename,
			'return_data' => $return_data
		);
		if (file_exists($filename))
		{
			// Check the extension
			$ext = $this->check_extension($filename);;
			if ($ext !== false)
			{
				$return = array_merge($return, array(
					'image_fullpath'  => $filename,
					'image_directory' => dirname($filename),
					'image_filename'  => basename($filename),
					'image_extension' => $ext
				));
				if ( ! $return_data)
				{
					$this->image_fullpath = $filename;
					$this->image_directory = dirname($filename);
					$this->image_filename = basename($filename);
					$this->image_extension = $ext;
				}
			}
			else
			{
				throw new \Fuel_Exception("The library does not support this filetype for <i>$filename</i>.");
			}
		}
		else
		{
			throw new \Fuel_Exception("Image file <i>$filename</i> does not exist.");
		}
		return $return;
	}

	/**
	 * Crops the image using coordinates or percentages.
	 *
	 * Positive whole numbers or percentages are coordinates from the top left.
	 *
	 * Negative whole numbers or percentages are coordinates from the bottom right.
	 *
	 * @param   integer  $x1  X-Coordinate for first set.
	 * @param   integer  $y1  Y-Coordinate for first set.
	 * @param   integer  $x2  X-Coordinate for second set.
	 * @param   integer  $y2  Y-Coordinate for second set.
	 * @return  Image_Driver
	 */
	public function crop($x1, $y1, $x2, $y2)
	{
		$this->queue('crop', $x1, $y1, $x2, $y2);
		return $this;
	}

	/**
	 * Executes the crop event when the queue is ran.
	 *
	 * Formats the crop method input for use with driver specific methods
	 *
	 * @param   integer  $x1  X-Coordinate for first set.
	 * @param   integer  $y1  Y-Coordinate for first set.
	 * @param   integer  $x2  X-Coordinate for second set.
	 * @param   integer  $y2  Y-Coordinate for second set.
	 * @return  Array    An array of variables for the specific driver.
	 */
	protected function _crop($x1, $y1, $x2, $y2)
	{
		$y1 === null and $y1 = $x1;
		$x2 === null and $x2 = "-" . $x1;
		$y2 === null and $y2 = "-" . $y1;

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
	 * @param   integer  $width   The new width of the image.
	 * @param   integer  $height  The new height of the image.
	 * @param   boolean  $keepar  If false, allows stretching of the image.
	 * @param   boolean  $pad     Adds padding to the image when resizing.
	 * @return  Image_Driver
	 */
	public function resize($width, $height = null, $keepar = true, $pad = false)
	{
		$this->queue('resize', $width, $height, $keepar, $pad);
		return $this;
	}

	/**
	 * Executes the resize event when the queue is ran.
	 *
	 * Formats the resize method input for use with driver specific methods.
	 *
	 * @param   integer  $width   The new width of the image.
	 * @param   integer  $height  The new height of the image.
	 * @param   boolean  $keepar  If false, allows stretching of the image.
	 * @param   boolean  $pad     Adds padding to the image when resizing.
	 * @return  Array    An array of variables for the specific driver.
	 */
	protected function _resize($width, $height = null, $keepar = true, $pad = true)
	{
		if ($height == null or $width == null)
		{
			if ($height == null and substr($width, -1) == '%')
			{
				$height = $width;
			}
			elseif (substr($height, -1) == '%' and $width == null)
			{
				$width = $height;
			}
			else
			{
				$sizes = $this->sizes();
				if ($height == null and $width != null)
				{
					$height = $width * ($sizes->width / $sizes->height);
				}
				elseif ($height != null and $width == null)
				{
					$width = $height * ($sizes->height / $sizes->width);
				}
				else
				{
					throw new \Fuel_Exception("Width and height cannot be null.");
				}
			}
		}
		$origwidth  = $this->convert_number($width, true);
		$origheight = $this->convert_number($height, false);
		$width      = $origwidth;
		$height     = $origheight;
		$sizes      = $this->sizes();
		$x = 0;
		$y = 0;
		if ($keepar)
		{
			// See which is the biggest ratio
			$width_ratio  = $width / $sizes->width;
			$height_ratio = $height / $sizes->height;
			if ($width_ratio < $height_ratio)
			{
				$width = floor($sizes->width * $height_ratio);
			}
			else
			{
				$height = floor($sizes->height * $width_ratio);
			}
		}
		if ($pad)
		{
			$x = floor(($origwidth - $width) / 2);
			$y = floor(($origheight - $height) / 2);
		} else {
			$origwidth  = $width;
			$origheight = $height;
		}
		return array(
			'width'   => $width,
			'height'  => $height,
			'cwidth'  => $origwidth,
			'cheight' => $origheight,
			'x' => $x,
			'y' => $y
		);
	}

	public function crop_resize($width, $height = null)
	{
		$this->queue('crop_resize', $width, $height);
		return $this;
	}

	protected function _crop_resize($width, $height)
	{
		// Determine the crop size
		$sizes   = $this->sizes();
		$width   = $this->convert_number($width, true);
		$height  = $this->convert_number($height, false);
		$widthr  = $sizes->width / $width;
		$heightr = $sizes->height / $height;
		$x = $y = 0;
		if ($widthr < $heightr)
		{
			$this->_resize($width, null, true, false);
		}
		else
		{
			$this->_resize(null, $height, true, false);
		}
		$sizes = $this->sizes();
		$y = floor(($sizes->height - $height) / 2);
		$x = floor(($sizes->width - $width) / 2);
		$this->_crop($x, $y, $x + $width, $y + $height);
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

	/**
	 * Executes the rotate event when the queue is ran.
	 *
	 * Formats the rotate method input for use with driver specific methods
	 *
	 * @param	integer	$degrees	The degrees to rotate, negatives integers allowed.
	 * @return	Array	An array of variables for the specific driver.
	 */
	protected function _rotate($degrees)
	{
		$degrees %= 360;
		if ($degrees < 0)
		{
			$degrees = 360 + $degrees;
		}
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

	/**
	 * Executes the watermark event when the queue is ran.
	 *
	 * Formats the watermark method input for use with driver specific methods
	 *
	 * @param	string	$filename	The filename of the watermark file to use.
	 * @param	string	$position	The position of the watermark, ex: "bottom right", "center center", "top left"
	 * @param	integer	$padding	The amount of padding (in pixels) from the position.
	 * @return	Array	An array of variables for the specific driver.
	 */
	protected function _watermark($filename, $position, $padding = 5)
	{
		$filename = realpath($filename);
		$return = false;
		if (file_exists($filename) and $this->check_extension($filename, false))
		{
			$x = 0;
			$y = 0;
			$wsizes = $this->sizes($filename);
			$sizes  = $this->sizes();
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
			$this->debug("Watermark being placed at $x,$y");
			$return = array(
				'filename' => $filename,
				'x' => $x,
				'y' => $y,
				'padding' => $padding
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

	/**
	 * Executes the border event when the queue is ran.
	 *
	 * Formats the border method input for use with driver specific methods
	 *
	 * @param	integer	$size	The side of the border, in pixels.
	 * @param	string	$color	A hexidecimal color.
	 * @return	Array	An array of variables for the specific driver.
	 */
	protected function _border($size, $color = null)
	{
		empty($color) and $color = $this->config['bgcolor'];

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

	/**
	 * Executes the mask event when the queue is ran.
	 *
	 * Formats the mask method input for use with driver specific methods
	 *
	 * @param	string	$maskimage	The location of the image to use as the mask
	 * @return	Array	An array of variables for the specific driver.
	 */
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

	public function _round_border($radius, $borderwidth, $color)
	{
		$this->rounded($radius);
	}

	/**
	 * Executes the rounded event when the queue is ran.
	 *
	 * Formats the rounded method input for use with driver specific methods
	 *
	 * @param	integer	$radius
	 * @param	integer	$sides	Accepts any combination of "tl tr bl br" seperated by spaces, or null for all sides
	 * @param	integer	$antialias	Sets the antialias range.
	 * @return	Array	An array of variables for the specific driver.
	 */
	protected function _rounded($radius, $sides, $antialias)
	{
		$radius < 0 and $radius = 0;
		$tl = $tr = $bl = $br = $sides == null;

		if ($sides != null)
		{
			$sides = explode(' ', $sides);
			foreach ($sides as $side)
			{
				if ($side == 'tl' || $side == 'tr' || $side == 'bl' || $side == 'br')
				{
					$$side = true;
				}
			}
		}
		$antialias == null and $antialias = 1;

		return array(
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
		if ( ! is_dir($directory))
		{
			throw new \Fuel_Exception("Could not find directory \"$directory\"");
		}

		// Touch the file
		if ( ! touch($filename))
		{
			throw new \Fuel_Exception("Do not have permission to write to \"$filename\"");
		}

		// Set the new permissions
		if ($permissions != null and ! chmod($filename, $permissions))
		{
			throw new \Fuel_Exception("Could not set permissions on the file.");
		}

		if ( ! $this->check_extension($filename, true))
		{
			$filename .= "." . $this->image_extension;
		}

		$this->debug("", "Saving image as <code>$filename</code>");
		return array(
			'filename' => $filename
		);
	}

	/**
	 * Saves the file in the original location, adding the append and prepend to the filename.
	 *
	 * @param  string   $append       The string to append to the filename
	 * @param  string   $prepend      The string to prepend to the filename
	 * @param  string   $extension    The extension to save the image as, null defaults to the loaded images extension.
	 * @param  integer  $permissions  The permissions to attempt to set on the file.
	 */
	public function save_pa($append, $prepend = null, $extension = null, $permissions = null)
	{
		$filename = substr($this->image_filename, 0, -(strlen($this->image_extension) + 1));
		$fullpath = $this->image_directory.'/'.$append.$filename.$prepend.'.'.($extension !== null ? $extension : $this->image_extension);
		$this->save($fullpath, $permissions);
		return $this;
	}

	/**
	 * Outputs the file directly to the user.
	 *
	 * @param	string	$filetype	The extension type to use. Ex: png, jpg, gif
	 */
	public function output($filetype = null)
	{
		if ($filetype == null)
		{
			$filetype = $this->config['filetype'] == null ? $this->image_extension : $this->config['filetype'];
		}

		if ($this->check_extension($filetype, false))
		{
			if ( ! $this->config['debug'])
			{
				header('Content-Type: image/' . $filetype);
			}
		}
		else
		{
			throw new \Fuel_Exception("Image extension $filetype is unsupported.");
		}

		$this->debug('', "Outputting image as $filetype");
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
	 * Adds a background to the image using the 'bgcolor' config option.
	 */
	abstract protected function add_background();

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
		foreach ($this->accepted_extension AS $ext)
		{
			if (substr($filename, strlen($ext) * -1) == $ext)
			{
				$writevar and $this->image_extension = $ext;
				$return = $ext;
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
		$tmpfunc = array();
		for ($i = 0; $i < count($func); $i++)
		{
			$tmpfunc[$i] = var_export($func[$i], true);
		}

		$this->debug("Queued <code>" . implode(", ", $tmpfunc) . "</code>");
		$this->queued_actions[] = $func;
	}

	/**
	 * Runs all queued actions on the loaded image.
	 *
	 * @param	boolean	$clear	Decides if the queue should be cleared once completed.
	 */
	public function run_queue($clear = null)
	{
		foreach ($this->queued_actions AS $action)
		{
			$tmpfunc = array();
			for ($i = 0; $i < count($action); $i++)
			{
				$tmpfunc[$i] = var_export($action[$i], true);
			}
			$this->debug('', "<b>Executing <code>" . implode(", ", $tmpfunc) . "</code></b>");
			call_user_func_array(array(&$this, '_' . $action[0]), array_slice($action, 1));
		}
		if (($clear === null && $this->config['clear_queue']) || $clear === true)
		{
			$this->queued_actions = array();
		}
	}

	/**
	 * Used for debugging image output.
	 *
	 * @param	string	$message
	 */
	protected function debug()
	{
		if ($this->config['debug'])
		{
			$messages = func_get_args();
			foreach ($messages as $message)
			{
				echo '<div>' . $message . '&nbsp;</div>';
			}
		}
	}
}

// End of file driver.php