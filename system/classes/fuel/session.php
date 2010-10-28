<?php defined('SYSPATH') or die('No direct script access.');
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

// --------------------------------------------------------------------

/**
 * Session Class
 *
 * @package		Fuel
 * @category	Sessions
 * @author		Harro "WanWizard" Verton
 */

class Fuel_Session
{
	public static function instance(array $config = array())
	{
		if (empty($config))
		{
			if (($config = Config::get('session')) === false)
			{
				Config::load('session', 'session');
				$config = Config::get('session');
			}
		}

		$driver = 'Session_'.ucfirst($config['type']).'_Driver';
		return new $driver($config);
	}

}

/* End of file session.php */
