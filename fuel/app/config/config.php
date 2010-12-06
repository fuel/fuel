<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Fuel Development Team
 * @license		MIT License
 * @copyright	2010 Dan Horrigan
 * @link		http://fuelphp.com
 */

namespace Fuel\Application;

return array(

	/**
	 * index_file - The name of the main bootstrap file.
	 *
	 * Set this to false or remove if you using mod_rewrite.
	 */
	'index_file'	=> 'index.php',

	/**
	 * Your environment.  Can be set to any of the following:
	 *
	 * Env::DEVELOPMENT
	 * Env::TEST
	 * Env::QA
	 * Env::PRODUCTION
	 */
	'environment'	=> Env::DEVELOPMENT,

	/**
	 * Show notices
	 *
	 * Some helper functions return false instead of an expected return type on invalid input,
	 * do you want Fuel to show notices explaining why false was returned?
	 * Even when true, only shows when environment is not PRODUCTION
	 */
	'show_notices'	=> true,

	/**
	 * Error throttling
	 *
	 * Limits the number of errors that receive full reporting and/or logging to prevent
	 * out-of-memory crashes.
	 */
	'error_throttling'	=> 10,

	'language'		=> 'en',

	'locale'		=> 'en_US',

	/**
	 * DateTime settings
	 *
	 * server_gmt_offset	in seconds the server offset from gmt timestamp when time() is used
	 * default_timezone		optional, if you want to change the server's default timezone
	 */
	'server_gmt_offset'	=> 0,
	//'default_timezone'	=> 'UTC',

	/**
	 * Logging Threshold.  Can be set to any of the following:
	 *
	 * Log::NONE
	 * Log::ERROR
	 * Log::DEBUG
	 * Log::INFO
	 * Log::ALL
	 */
	'log_threshold'		=> Log::ERROR,
	'log_path'			=> APPPATH.'logs/',
	'log_date_format' 	=> 'Y-m-d H:i:s',

	/**
	 * Security settings
	 */
	'security' => array(
		'csrf_autoload'			=> false,
		'csrf_token_key'		=> 'fuel_csrf_token',
		'csrf_expiration'		=> 0,

		'strict_input_filters'	=> true,
		'input_filters'			=> array(
			'get'		=> array(
				'*'	=> 'no_tags',
			),
			'post'		=> array(
				'*'	=> 'no_tags',
			),
			'cookie'	=> array(),
			'env'		=> array(),
			'server'	=> array(),
			'files'		=> array(),
		),
	),

	/**
	 * These packages are loaded on Fuel's startup.  You can specify them in
	 * the following manner:
	 *
	 * array('auth'); // This will assume the packages are in PKGPATH
	 *
	 * // Use this format to specify the path to the package explicitly
	 * array(
	 *     array('auth'	=> PKGPATH.'auth/')
	 * );
	 */
	'packages'	=> array(),

	/**
	 * To enable you to split up your application into modules which can be
	 * routed by the first uri segment you have to define their basepaths
	 * here. By default empty, but to use them you can add something
	 * like this:
	 *      array(APPPATH.'modules'.DS)
	 */
	// 'module_paths' => array(APPPATH.'modules'.DS),

	'always_load'	=> array(

		/**
		 * Classes to autoload & initialize even when not used
		 */
		'classes'	=> array(),

		/**
		 * Configs to autoload
		 *
		 * Examples: if you want to load 'session' config into a group 'session' you only have to
		 * add 'session'. If you want to add it to another group (example: 'auth') you have to
		 * add it like 'session' => 'auth'.
		 * If you don't want the config in a group use null as groupname.
		 */
		'config'	=> array(),

		/**
		 * Language files to autoload
		 *
		 * Examples: if you want to load 'validation' lang into a group 'validation' you only have to
		 * add 'validation'. If you want to add it to another group (example: 'forms') you have to
		 * add it like 'validation' => 'forms'.
		 * If you don't want the lang in a group use null as groupname.
		 */
		'language'	=> array(),
	)
);

/* End of file config.php */