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

namespace Oil;

use Fuel\Application as App;

class Package
{
	public function install($package, $version = null)
	{
		$config = App\Config::load('package');

		$version = App\Cli::option('version', 'master');

		$package_found = FALSE;
		
		foreach ($config['sources'] as $source)
		{
			$zip_url = rtrim($source, '/').'/fuel-'.$package.'/zipball/'.$version;

			if ($fp = fopen($zip_url, 'r'))
			{
				App\Cli::write('Downloading package: '.$zip_url);

				$package_found = TRUE;

				$content = '';
				
				// keep reading until there's nothing left
				while ($line = fgets($fp, 1024))
				{
					$content .= $line;
				}

				break;
			}
		}

		if ($package_found === TRUE)
		{
			App\Cli::write('Installing package "' . $package . '"');
		}

		else
		{
			App\Cli::write('Package "' . $package . '" could not be found.');
			return;
		}

		$zip_file = APPPATH . 'tmp/' . $package.'-'.time().'.zip';
		file_put_contents($zip_file, $contents);

		$unzip = new App\Unzip;
		$unzip->extract($zip_file);

		
	}
}

/* End of file model.php */