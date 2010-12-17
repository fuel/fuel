<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Dan Horrigan <http://dhorrigan.com>
 * @license		MIT License
 * @copyright	2010 Dan Horrigan
 * @link		http://fuelphp.com
 */

namespace Fuel\App;

return array(

	/**
	 * A couple of named patterns that are often used
	 */
	'patterns' => array(
		'local'		=> '%c',

		'us'		=> '%m/%d/%Y',
		'us_short'	=> '%m/%d',
		'us_named'	=> '%B %d %Y',
		'us_full'	=> '%I:%M %p, %B %d %Y',
		'eu'		=> '%d/%m/%Y',
		'eu_short'	=> '%d/%m',
		'eu_named'	=> '%d %B %Y',
		'eu_full'	=> '%H:%M, %d %B %Y',

		'24h'		=> '%H:%M',
		'12h'		=> '%I:%M %p'
	)
);

/* End of file config/date.php */