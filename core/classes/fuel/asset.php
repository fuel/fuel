<?php defined('COREPATH') or exit('No direct script access allowed');

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

class Fuel_Asset {

	/**
	 * @var	array	The asset paths
	 */
	protected static $_asset_paths = array();

	/**
	 * @var	string	The URL to be prepended to all assets
	 */
	protected static $_asset_url = '/';

	/**
	 * @var	string	The folder names
	 */
	protected static $_folders = array(
		'css'	=>	'css/',
		'js'	=>	'js/',
		'img'	=>	'img/',
	);

	/**
	 * @var	array	Holds the groups of assets
	 */
	protected static $_groups = array();

	/**
	 * @var	bool	Get this baby going
	 */
	public static $initialized = false;

	// --------------------------------------------------------------------

	/**
	 * Init
	 *
	 * Loads in the config and sets the variables
	 *
	 * @access	public
	 * @return	void
	 */
	public static function _init()
	{
		// Prevent multiple initializations
		if (self::$initialized)
		{
			return;
		}

		Config::load('asset', 'asset');

		$paths = Config::get('asset.paths');

		foreach($paths as $path)
		{
			self::add_path($path);
		}

		self::$_asset_url = Config::get('asset.url');

		self::$_folders = array(
			'css'	=>	Config::get('asset.css_dir'),
			'js'	=>	Config::get('asset.js_dir'),
			'img'	=>	Config::get('asset.img_dir')
		);

		self::$initialized = true;
	}

	// --------------------------------------------------------------------

	/**
	 * Add Path
	 *
	 * Adds the given path to the front of the asset paths array
	 *
	 * @access	public
	 * @param	string	The path to add
	 * @return	void
	 */
	public static function add_path($path)
	{
		array_unshift(self::$_asset_paths, str_replace('../', '', $path));
	}

	// --------------------------------------------------------------------

	/**
	 * Remove Path
	 *
	 * Removes the given path from the asset paths array
	 *
	 * @access	public
	 * @param	string	The path to remove
	 * @return	void
	 */
	public static function remove_path($path)
	{
		if (($key = array_search(str_replace('../', '', $path), self::$_asset_paths)) !== false)
		{
			unset(self::$_asset_paths[$key]);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Render
	 *
	 * Renders the group of assets and returns the tags.
	 *
	 * @access	public
	 * @param	mixed	The group to render
	 * @param	bool	Whether to return the raw file or not
	 * @return	string	The group's output
	 */
	public static function render($group, $raw = false)
	{
		if (is_string($group))
		{
			$group = isset(self::$_groups[$group]) ? self::$_groups[$group] : array();
		}

		$return = '';
		foreach ($group as $key => $item)
		{
			$type = $item['type'];
			$filename = $item['file'];
			$attr = $item['attr'];

			if (strpos($filename, '://') === false)
			{
				if ( ! ($file = self::find_file($filename, self::$_folders[$type])))
				{
					throw new Fuel_Exception('Could not find asset: '.$filename);
				}
				
				$file = self::$_asset_url.$file;
			}
			else
			{
				$file = $filename;
			}

			switch($type)
			{
				case 'css':
					if ($raw)
					{
						return '<style type="text/css">'.PHP_EOL.file_get_contents($file).PHP_EOL.'</style>';
					}
					$attr['rel'] = 'stylesheet';
					$attr['type'] = 'text/css';
					$attr['href'] = $file;

					$return .= '<link'.self::attr($attr).' />'.PHP_EOL;
					break;
				case 'js':
					if ($raw)
					{
						return '<script type="text/javascript">'.PHP_EOL.file_get_contents($file).PHP_EOL.'</script>';
					}
					$attr['type'] = 'text/javascript';
					$attr['src'] = $file;

					$return .= '<script'.self::attr($attr).'></script>'.PHP_EOL;
					break;
				case 'img':
					$attr['src'] = $file;
					$attr['alt'] = isset($attr['alt']) ? $attr['alt'] : '';

					$return .= '<img'.self::attr($attr).' />';
					break;
			}

		}
		
		return $return;
	}

	// --------------------------------------------------------------------

	/**
	 * CSS
	 *
	 * Either adds the stylesheet to the group, or returns the CSS tag.
	 *
	 * @access	public
	 * @param	mixed	The file name, or an array files.
	 * @param	array	An array of extra attributes
	 * @param	string	The asset group name
	 * @return	string
	 */
	public static function css($stylesheets = array(), $attr = array(), $group = NULL, $raw = false)
	{
		static $temp_group = 1000000;

		$render = false;
		if ($group === NULL)
		{
			$group = (string) (++$temp_group);
			$render = true;
		}

		self::_parse_assets('css', $stylesheets, $attr, $group);

		if ($render)
		{
			return self::render($group, $raw);
		}

		return '';
	}

	// --------------------------------------------------------------------

	/**
	 * JS
	 *
	 * Either adds the javascript to the group, or returns the script tag.
	 *
	 * @access	public
	 * @param	mixed	The file name, or an array files.
	 * @param	array	An array of extra attributes
	 * @param	string	The asset group name
	 * @return	string
	 */
	public static function js($scripts = array(), $attr = array(), $group = NULL, $raw = false)
	{
		static $temp_group = 2000000;

		$render = false;
		if ( ! isset($group))
		{
			$group = (string) $temp_group++;
			$render = true;
		}

		self::_parse_assets('js', $scripts, $attr, $group);

		if ($render)
		{
			return self::render($group, $raw);
		}

		return '';
	}

	// --------------------------------------------------------------------

	/**
	 * Img
	 *
	 * Either adds the image to the group, or returns the image tag.
	 *
	 * @access	public
	 * @param	mixed	The file name, or an array files.
	 * @param	array	An array of extra attributes
	 * @param	string	The asset group name
	 * @return	string
	 */
	public static function img($images = array(), $attr = array(), $group = NULL)
	{
		static $temp_group = 3000000;

		$render = false;
		if ( ! isset($group))
		{
			$group = (string) $temp_group++;
			$render = true;
		}

		self::_parse_assets('img', $images, $attr, $group);

		if ($render)
		{
			return self::render($group);
		}

		return '';
	}

	// --------------------------------------------------------------------

	/**
	 * Parse Assets
	 *
	 * Pareses the assets and adds them to the group
	 *
	 * @access	private
	 * @param	string	The asset type
	 * @param	mixed	The file name, or an array files.
	 * @param	array	An array of extra attributes
	 * @param	string	The asset group name
	 * @return	string
	 */
	protected static function _parse_assets($type, $assets, $attr, $group)
	{
		if ( ! is_array($assets))
		{
			$assets = array($assets);
		}
		
		foreach ($assets as $key => $asset)
		{
			self::$_groups[$group][] = array(
				'type'	=>	$type,
				'file'	=>	$asset,
				'attr'	=>	(array) $attr
			);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Attr
	 *
	 * Converts an array of attribute into a string
	 *
	 * @access	public
	 * @param	array	The attribute array
	 * @return	string	The attribute string
	 * @return	string
	 */
	public static function attr($attributes = NULL)
	{
		if (empty($attributes))
		{
			return '';
		}

		$final = '';
		foreach ($attributes as $key => $value)
		{
			if ($value === NULL)
			{
				continue;
			}

			$final .= ' '.$key.'="'.htmlspecialchars($value, ENT_QUOTES).'"';
		}

		return $final;
	}

	// --------------------------------------------------------------------

	/**
	 * Find File
	 *
	 * Locates a file in all the asset paths.
	 *
	 * @access	public
	 * @param	string	The filename to locate
	 * @param	string	The sub-folder to look in
	 * @return	mixed	Either the path to the file or false if not found
	 */
	public static function find_file($file, $folder)
	{
		foreach (self::$_asset_paths as $path)
		{
			if (is_file($path.$folder.$file))
			{
				return $path.$folder.$file;
			}
		}
		
		return false;
	}
}

/* End of file asset.php */