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

class Image {

	protected static $_instance = null;

	/**
	 * Creates a new instance for static use of the class.
	 *
	 * @return  Image_Driver
	 */
	protected static function instance()
	{
		if (Image::$_instance == null)
		{
			Image::$_instance = Image::factory();
		}
		return Image::$_instance;
	}

	/**
	 * Creates a new instance of the email driver
	 *
	 * @param  array   $config
	 * @return Image_Driver
	 */
	public static function factory($config = array(), $filename = null)
	{
		$protocol = ucfirst( ! empty($config['driver']) ? $config['driver'] : 'gd');
		$class = 'Image_'.$protocol;
		if ($protocol == 'Driver' || ! class_exists($class))
		{
			throw new \Fuel_Exception('Protocol '.$protocol.' is not a valid protocol for emailing.');
		}
		$return = new $class($config);
		if ($filename !== null)
		{
			$return->load($filename);
		}
		return $return;
	}

	/**
	 * Used to set configuration options.
	 *
	 * @param  array   $config   An array of configuration settings.
	 * @return Image_Driver
	 */
	public static function config($config = array())
	{
		return Image::instance()->config($config);
	}

	/**
	 * Loads the image and checks if its compatable.
	 *
	 * @param  string  $filename  The file to load
	 * @return Image_Driver
	 */
	public static function load($filename)
	{
		return Image::instance()->load($filename);
	}

	/**
	 * Crops the image using coordinates or percentages.
	 *
	 * Absolute integer or percentages accepted for all 4.
	 *
	 * @param  integer  $x1  X-Coordinate based from the top-left corner.
	 * @param  integer  $y1  Y-Coordinate based from the top-left corner.
	 * @param  integer  $x2  X-Coordinate based from the bottom-right corner.
	 * @param  integer  $y2  Y-Coordinate based from the bottom-right corner.
	 * @return Image_Driver
	 */
	public static function crop($x1, $y1, $x2, $y2)
	{
		return Image::instance()->crop($x1, $y1, $x2, $y2);
	}

	/**
	 * Resizes the image. If the width or height is null, it will resize retaining the original aspect ratio.
	 *
	 * @param  integer  $width   The new width of the image.
	 * @param  integer  $height  The new height of the image.
	 * @param  boolean  $keepar  Defaults to true. If false, allows resizing without keeping AR.
	 * @param  boolean  $pad     If set to true and $keepar is true, it will pad the image with the configured bgcolor
	 * @return Image_Driver
	 */
	public static function resize($width, $height, $keepar = true, $pad = false)
	{
		return Image::instance()->resize($width, $height, $keepar, $pad);
	}

	/**
	 * Resizes the image. If the width or height is null, it will resize retaining the original aspect ratio.
	 *
	 * @param  integer  $width   The new width of the image.
	 * @param  integer  $height  The new height of the image.
	 * @return Image_Driver
	 */
	public static function crop_resize($width, $height)
	{
		return Image::instance()->crop_resize($width, $height);
	}

	/**
	 * Rotates the image
	 *
	 * @param  integer  $degrees  The degrees to rotate, negatives integers allowed.
	 * @return Image_Driver
	 */
	public static function rotate($degrees)
	{
		return Image::instance()->rotate($degrees);
	}

	/**
	 * Adds a watermark to the image.
	 *
	 * @param  string   $filename  The filename of the watermark file to use.
	 * @param  string   $position  The position of the watermark, ex: "bottom right", "center center", "top left"
	 * @param  integer  $padding   The spacing between the edge of the image.
	 * @return Image_Driver
	 */
	public static function watermark($filename, $position, $padding = 5)
	{
		return Image::instance()->watermark($filename, $position, $padding);
	}

	/**
	 * Adds a border to the image.
	 *
	 * @param  integer  $size   The side of the border, in pixels.
	 * @param  string   $color  A hexidecimal color.
	 * @param  Image_Driver
	 */
	public static function border($size, $color = null)
	{
		return Image::instance()->border($size, $color);
	}

	/**
	 * Masks the image using the alpha channel of the image input.
	 *
	 * @param  string  $maskimage  The location of the image to use as the mask
	 * @return Image_Driver
	 */
	public static function mask($maskimage)
	{
		return Image::instance()->mask($maskimage);
	}

	/**
	 * Adds rounded corners to the image.
	 *
	 * @param  integer  $radius
	 * @param  integer  $sides      Accepts any combination of "tl tr bl br" seperated by spaces, or null for all sides
	 * @param  integer  $antialias  Sets the antialias range.
	 * @return Image_Driver
	 */
	public static function rounded($radius, $sides = null, $antialias = null)
	{
		return Image::instance()->rounded($radius, $sides, $antialias);
	}

	/**
	 * Saves the image, and optionally attempts to set permissions
	 *
	 * @param  string  $filename     The location where to save the image.
	 * @param  string  $permissions  Allows unix style permissions
	 * @return string  The location of the file
	 */
	public static function save($filename, $permissions = null)
	{
		return Image::instance()->save($filename, $permissions);
	}

	/**
	 * Saves the image, and optionally attempts to set permissions
	 *
	 * @param  string  $prepend      The text to add to the beginning of the filename.
	 * @param  string  $append       The text to add to the end of the filename.
	 * @param  string  $permissions  Allows unix style permissions
	 * @return string  The location of the file
	 */
	public static function save_pa($prepend, $append = null, $permissions = null)
	{
		return Image::instance()->save_pa($prepend, $append, $permissions);
	}

	/**
	 * Outputs the file directly to the user.
	 *
	 * @param  string  $filetype  The extension type to use. Ex: png, jpg, bmp, gif
	 */
	public static function output($filetype = null)
	{
		Image::instance()->output($filetype);
	}

}

// End of file image.php