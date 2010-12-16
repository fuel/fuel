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
	'environment'	=> Fuel::DEVELOPMENT,

	'profile'		=> false,
	
	'caching'			=> false,
	'cache_dir'			=> APPPATH.'cache/',
	'cache_lifetime'	=> 3600, // In Seconds

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


	/**************************************************************************/
	/* Always Load                                                            */
	/**************************************************************************/

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
	),

	/**************************************************************************/
	/* Routes                                                                 */
	/**************************************************************************/

	'routes'	=> array(

		// This is the default route.  We use a "#" here so that we do not have any
		// reserved routes.
		'#'	=> 'welcome',

		'404'		=> 'welcome/404',

		'hello/:name'	=> 'test/hello',

		/**
		 * Basic Routing
		 *
		 * The route on the left is matched the request URI and if it is
		 * matched then the request is routed to the URI on the right.
		 *
		 * This allows you do do things like the following:
		 *
		 * 'about'		=> 'site/about',
		 * 'contact'	=> 'contact/form',
		 * 'admin'		=> 'admin/login',
		 */

		/**
		 * Slightly Advanced Routing
		 *
		 * You can include any regex into your routes.  The left side is matched against the
		 * requests URI, and the right side is the replacement for the left, so you can use
		 * backreferences in the right side from the regex on the left.  There are also a few
		 * special statements that allow you match anything, or just a segment:
		 *
		 * :any - This matches anything from that point on in the URI
		 * :segment - This matches only 1 segment in the URI, but that segment can be anything
		 *
		 * Here are some examples:
		 *
		 * 'blog/(:any)'		=> 'blog/entry/$1', // Routes /blog/entry_name to /blog/entry/entry_name
		 * '(:segment)/about'	=> 'site/about/$1', // Routes /en/about to /site/about/en
		 */

		/**
		 * Advanced Routing
		 *
		 * You can also have named parameters in your routes.  This allows you to give your URI segments
		 * names, which can then be accessed from within your actions.
		 *
		 * Example:
		 *
		 * 'blog/:year/:month/:id'	=> 'blog/entry', // Routes /blog/2010/11/entry_name to /blog/entry
		 *
		 * In the above example it would catch the following '/blog/2010/11/entry_name'.  It would then
		 * route that request to your 'entry' action in your 'blog' controller.  There, the named params
		 * will be available like this:
		 *
		 * $this->param('year');
		 * $this->param('month');
		 * $this->param('id');
		 */
		
		/**
		 * HTTP verb based routing
		 *
		 * You can route your URLs to controllers and actions based on the HTTP verb used to call them.
		 * This makes it quick and easy to make RESTful controllers.
		 *
		 * Example:
		 *
		 * 'blog' => array(array('GET', 'blog/all'), array('POST', 'blog/create')), // Routes GET /blog to /blog/all and POST /blog to /blog/create
		 *
		 * You can use named parameters and regexes within your URL just like normal:
		 *
		 * 'blog/(:any)' => array(array('GET', 'blog/show/$1'))
		 */
	),
);

/* End of file config.php */