<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Dan Horrigan <http://dhorrigan.com>
 * @license		Apache License v2.0
 * @copyright	2010 Dan Horrigan
 */

return array(

	/**
	 * Your environment.  Can be set to any of the following:
	 *
	 * Env::TEST
	 * Env::DEVELOPMENT
	 * Env::QA
	 * Env::PRODUCTION
	 */
	'environment'	=> Env::DEVELOPMENT,

	/**
	 * index_file - The name of the main bootstrap file.
	 *
	 * Set this to FALSE or remove if you using mod_rewrite.
	 */
	'index_file'	=> 'index.php',
);

/* End of file config.php */