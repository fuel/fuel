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
 * @copyright	2010 - 2011 Fuel Development Team
 * @link		http://fuelphp.com
 */

namespace Oil;

/**
 * Oil\Package Class
 *
 * @package		Fuel
 * @subpackage	Oil
 * @category	Core
 * @author		Phil Sturgeon
 */
class Package
{
	protected static $protected = array('auth', 'activerecord', 'octane', 'oil');

	public static function install($package = null)
	{
		// Make sure something is set
		if ($package === null)
		{
			static::help();
			return;
		}

		$config = \Config::load('package');

		$version = \Cli::option('version', 'master');

		// Check to see if this package is already installed
		if (is_dir(PKGPATH . $package))
		{
			throw new Exception('Package "' . $package . '" is already installed.');
			return;
		}

		$package_found = FALSE;
		
		foreach ($config['sources'] as $source)
		{
			$zip_url = rtrim($source, '/').'/fuel-'.$package.'/zipball/'.$version;

			if ($fp = @fopen($zip_url, 'r'))
			{
				\Cli::write('Downloading package: '.$zip_url);

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
			\Cli::write('Installing package "' . $package . '"');
		}

		else
		{
			throw new Exception('Package "' . $package . '" could not be found.');
			return false;
		}

		// Make the folder so we can extract the ZIP to it
		mkdir($tmp_folder);

		$unzip = new \Unzip;
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
			\Cli::write("\t" . $path);
		}
	}


	public static function uninstall($package)
	{
		$package_folder = PKGPATH . $package;

		// Check to see if this package is already installed
		if (in_array($package, static::$protected))
		{
			throw new Exception('Package "' . $package . '" cannot be uninstalled.');
			return false;
		}

		// Check to see if this package is already installed
		if ( ! is_dir($package_folder))
		{
			throw new Exception('Package "' . $package . '" is not installed.');
			return false;
		}

		\Cli::write('Uninstalling package "' . $package . '"', 'green');

		\File::delete_dir($package_folder);
	}


	public static function help()
	{
		\Cli::write('Help coming soon!', 'blue');
	}
}

/* End of file package.php */