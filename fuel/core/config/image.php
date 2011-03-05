<?php
/**
 * Email config for use with the email package.
 */

return array(
	/**
	 * The driver to be used. Currently gd or imagemagick
	 */
	'driver' => 'imagemagick',

	/**
	 * Sets the background color of the image.
	 *
	 * Set to null for a transparent background.
	 */
	'bgcolor' => null,

	/**
	 * Sets the transparency of any watermark added to the image.
	 */
	'watermark_alpha' => 75,

	/**
	 * The quality of the image being saved or output, if the format supports it.
	 */
	'quality' => 100,

	/**
	 * Lets you use a default container for images. Override by Image::output('png') or Image::save('file.png')
	 *
	 * Examples: png, bmp, jpeg, ...
	 */
	'filetype' => null,

	/**
	 * The install location of the imagemagick executables.
	 */
	'imagemagick_dir' => 'C:/wamp/imagemagick/',

	/**
	 * Temporary directory to store image files in that are being edited.
	 */
	'temp_dir' => 'C:/wamp/tmp/',

	/**
	 * The string of text to append to the image.
	 */
	'temp_append' => 'fuelimage_',

	/**
	 * Sets the default antialias.
	 */
	'antialias' => 1,
	
	/**
	 * These presets allow you to call controlled manipulations.
	 */
	'presets' => array(

		/**
		 * This shows an example of how to add preset manipulations
		 * to an image.
		 *
		 * Note that config values here override the current configuration.
		 *
		 * Libraries cannot be changed in here. (TODO - Add this feature)
		 */
		'example' => array(
			'quality' => 100,
			'bgcolor' => '#FFFFFF',
			'bgalpha' => 25,
			'actions' => array(
				//array('rotate', -45),
				//array('watermark', '/var/www/watermark.png', 'bottom right', 10),
				//array('resize', '25%', null, true, false),
				/**
				 * Variables passed to the preset function (such as $this->preset('example', '/www/public/images/image.png) )
				 * can be used to set variables in the presets. In this function, the $1 would be replaced by
				 * '/www/public/images/image.png'
				 */
				array('resize', 600),
				array('border', 10, '#00FF00'),
				array('output', 'png')
			)
		)
	)
);

/* End of file email.php */
