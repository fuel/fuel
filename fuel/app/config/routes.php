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
	'default'	=> 'welcome',
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
);

/* End of file routes.php */