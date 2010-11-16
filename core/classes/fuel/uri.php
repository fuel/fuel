<?php defined('COREPATH') or die('No direct script access.');
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

class Fuel_URI {

	public static function detect()
	{
		// We want to use PATH_INFO if we can.
		if ( ! empty($_SERVER['PATH_INFO']))
		{
			$uri = $_SERVER['PATH_INFO'];
		}
		else
		{
			if (isset($_SERVER['REQUEST_URI']))
			{
				// Some servers require 'index.php?' as the index page
				// if we are using mod_rewrite or the server does not require
				// the question mark, then parse the url.
				if (Config::get('index_file') != 'index.php?')
				{
					$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
				}
				else
				{
					$uri = $_SERVER['REQUEST_URI'];
				}
			}
			else
			{
				throw new Fuel_Exception('Unable to detect the URI.');
			}

			// Remove the base URL from the URI
			$base_url = parse_url(Config::get('base_url'), PHP_URL_PATH);
			if ($uri != '' and strncmp($uri, $base_url, strlen($base_url)) === 0)
			{
				$uri = substr($uri, strlen($base_url));
			}

			// If we are using an index file (not mod_rewrite) then remove it
			$index_file = Config::get('index_file');
			if ($index_file and strncmp($uri, $index_file, strlen($index_file)) === 0)
			{
				$uri = substr($uri, strlen($index_file));
			}

			// Lets split the URI up in case it containes a ?.  This would
			// indecate the server requires 'index.php?' and that mod_rewrite
			// is not being used.
			preg_match('#(.*?)\?(.*)#i', $uri, $matches);

			// If there are matches then lets set set everything correctly
			if ( ! empty($matches))
			{
				$uri = $matches[1];
				$_SERVER['QUERY_STRING'] = $matches[2];
				parse_str($matches[2], $_GET);
			}
		}

		// Do some final clean up of the uri
		$uri = str_replace(array('//', '../'), '/', $uri);

		return $uri;
	}

	/**
	 * Returns the desired segment, or false if it does not exist.
	 *
	 * @access	public
	 * @param	int		The segment number
	 * @return	string
	 */
	public static function segment($segment)
	{
		if (isset(Request::active()->uri->segments[$segment - 1]))
		{
			return Request::active()->uri->segments[$segment - 1];
		}

		return false;
	}

	public static function string()
	{
		return Request::active()->uri->uri;
	}

	/**
	 * @var	string	The URI string
	 */
	public $uri = '';

	/**
	 * @var	array	The URI segements
	 */
	public $segments = '';

	/**
	 * Contruct takes a URI or detects it if none is given and generates
	 * the segments.
	 *
	 * @access	public
	 * @param	string	The URI
	 * @return	void
	 */
	public function __construct($uri = NULL)
	{
		if ($uri === NULL)
		{
			$uri = URI::detect();
		}
		$this->uri = trim($uri, '/');
		$this->segments = explode('/', $this->uri);
	}
	
	public function __toString()
	{
		return $this->uri;
	}
}

/* End of file uri.php */