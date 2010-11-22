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

	'language'		=> 'en',
	
	'locale'		=> 'en_US',

	/**
	 * Logging Threshold.  Can be set to any of the following:
	 *
	 * Log::NONE
	 * Log::ERROR
	 * Log::DEBUG
	 * Log::INFO
	 * Log::ALL
	 */
	'log_threshold'		=> Log::ALL,
	'log_path'			=> APPPATH . 'logs/',
	'log_date_format' 	=> 'Y-m-d H:i:s',
	
	/**
	 * These packages are loaded on Fuel's startup.  You can specify them in
	 * the following manner:
	 * 
	 * array('db', 'auth'); // This will assume the packages are in PKGPATH
	 * 
	 * // Use this format to specify the path to the package explicitly
	 * array(
	 *     array('db'	=> PKGPATH.'db/')
	 *     array('auth'	=> PKGPATH.'auth/')
	 * );
	 */
	'packages'	=> array(),

);

/* End of file config.php */