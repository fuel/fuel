<?php

// Bootstrap the framework DO NOT edit this
require_once COREPATH.'bootstrap.php';

/**
 * Set the timezone to what you need it to be.
 */
date_default_timezone_set('UTC');

/**
 * Set the encoding you would like to use.
 */
Fuel::$encoding = 'UTF-8';


Autoloader::add_classes(array(
	// Add classes you want to override here
	// Example: 'View' => APPPATH.'classes/view.php',
));

// Register the autoloader
Autoloader::register();

// Initialize the framework with the config file.
Fuel::init(include(APPPATH.'config/config.php'));


/* End of file bootstrap.php */