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

namespace Fuel\Tasks;

/**
 * Install task
 *
 * Run this task to set default write permissions and environment stuff
 * for your app. This could be expanded in app/tasks for applicaiton specific stuff.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Phil Sturgeon
 */

class Install {

	public static function run()
	{
		$writable_paths = array(
			APPPATH . 'cache',
			APPPATH . 'logs',
			APPPATH . 'tmp'
		);

		foreach ($writable_paths as $path)
		{
			if (@chmod($path, 0777))
			{
				\Cli::write("\t" . \Cli::color('Made writable: ' . \Fuel::clean_path($path), 'green'));
			}

			else
			{
				\Cli::write("\t" . \Cli::color('Failed to make writable: ' . \Fuel::clean_path($path), 'red'));
			}
		}
	}
}

/* End of file tasks/install.php */