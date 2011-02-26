<?php
/**
 * Email config for use with the email package.
 */

return array(
	/**
	 * The driver to be used.
	 */
	'driver' => 'gd',

	/**
	 * Sets the background color of the image.
	 */
	'bgcolor' => '#FFFFFF',

	/**
	 * Sets the transparency of the background, 0 being transparent and 100 being opaque.
	 */
	'bgalpha' => 100,

	/**
	 * Sets the transparency of any watermark added to the image.
	 */
	'watermark_alpha' => 75,

	/**
	 * The quality of the image being saved or output, if the format supports it.
	 */
	'quality' => 80,

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
	'temp_dir' => 'C:\\wamp\tmp\\',

	/**
	 * The string of text to append to the image.
	 */
	'temp_append' => 'fuelimage_',
	
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
		 * Libraries cannot be changed in here.
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
