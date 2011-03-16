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
	 * Sets if the queue should be cleared after a save(), save_pa(), or output().
	 */
	'clear_queue' => false,

	/**
	 * Used to debug the class, defaults to false.
	 */
	'debug' => false,
	
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
			'bgcolor' => null,
			'actions' => array(
				array('crop_resize', 200, 200),
				array('border', 20, "#f00"),
				array('rounded', 10),
				array('output', 'png')
			)
		)
	)
);

/* End of file email.php */
