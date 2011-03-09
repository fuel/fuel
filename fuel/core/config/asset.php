<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2011 Fuel Development Team
 * @link       http://fuelphp.com
 */

return array(

	/**
	* An array of paths that will be searched for assets. Each asset is a
	* RELATIVE path from the base_url WITH a trailing slash:
	*
	* array('assets/')
	*/

	'paths' => array('assets/'),

	/**
	* URL to your Fuel root. Typically this will be your base URL,
	* WITH a trailing slash:
	*
	* Config::get('base_url')
	*/

	'url' => Config::get('base_url'),

	/**
	* Asset Sub-folders
	*
	* Names for the img, js and css folders (inside the asset path).
	*
	* Examples:
	*
	* img/
	* js/
	* css/
	*
	* This MUST include the trailing slash ('/')
	*/
	'img_dir' => 'img/',
	'js_dir' => 'js/',
	'css_dir' => 'css/'
);

/* End of file config/asset.php */