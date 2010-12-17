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

use Fuel\App as App;

class Package
{
	public function install($package, $version = null)
	{
		$config = App\Config::load('package');

		$version = App\Cli::option('version', 'master');

		// Check to see if this package is already installed
		if (is_dir(PKGPATH . $package))
		{
			App\Cli::write(App\Cli::color('Package "' . $package . '" is already installed.', 'red'));
			return;
		}

		$package_found = FALSE;
		
		foreach ($config['sources'] as $source)
		{
			$zip_url = rtrim($source, '/').'/fuel-'.$package.'/zipball/'.$version;

			if ($fp = @fopen($zip_url, 'r'))
			{
				App\Cli::write('Downloading package: '.$zip_url);

				$package_found = TRUE;

				$content = '';

				// We need somewhere to put the zip, make if missing
				if ( ! is_dir(APPPATH . 'tmp'))
				{
					mkdir(APPPATH . 'tmp');
				}
				
				// keep reading until there's nothing left
				$tmp_folder = APPPATH . 'tmp/' . $package . '-' . time();

				$zip_file = $tmp_folder . '.zip';
				@copy($zip_url, $zip_file);

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

		// Make the folder so we can extract the ZIP to it
		mkdir($tmp_folder);

		$unzip = new App\Unzip;
		$files = $unzip->extract($zip_file, $tmp_folder);

		// Grab the first folder out of it (we dont know what it's called)
		list($tmp_package_folder) = glob($tmp_folder.'/*', GLOB_ONLYDIR);

		$package_folder = PKGPATH . $package;

		// Move that folder into the packages folder
		rename($tmp_package_folder, $package_folder);

		unlink($zip_file);
		rmdir($tmp_folder);

		foreach ($files as $file)
		{
			$path = str_replace($tmp_package_folder, $package_folder, $file);
			chmod($path, octdec(755));
			App\Cli::write("\t" . $path);
		}
	}


	public function uninstall($package)
	{
		$package_folder = PKGPATH . $package;

		// Check to see if this package is already installed
		if ( ! is_dir($package_folder))
		{
			App\Cli::write(App\Cli::color('Package "' . $package . '" is not installed.', 'red'));
			return;
		}

		App\Cli::write('Uninstalling package "' . $package . '"');

		App\File::delete_dir($package_folder);
	}
}

/* End of file model.php */