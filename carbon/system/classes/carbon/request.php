<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Carbon
 *
 * Carbon is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Carbon
 * @version		1.0
 * @author		Dan Horrigan <http://dhorrigan.com>
 * @license		Apache License v2.0
 * @copyright	2010 Dan Horrigan
 */

class Carbon_Request {

	/**
	 * @var	array	An array of status codes and messages
	 */
	public static $status = array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		507 => 'Insufficient Storage',
		509 => 'Bandwidth Limit Exceeded'
	);

	/**
	 * @var	object	Holds the global request instance
	 */
	public static $instance = FALSE;
	
	/**
	 * @var	object	Holds the current request being executed
	 */
	public static $current = FALSE;

	/**
	 * @var	string	The request method
	 */
	public static $method = 'GET';

	/**
	 * @var	string	The request protocol
	 */
	public static $protocol = 'http';

	/**
	 * @var	string	Referring URL
	 */
	public static $referrer;

	/**
	 * @var	string	The client user agent
	 */
	public static $user_agent = '';

	/**
	 * @var	string	Client IP address
	 */
	public static $client_ip = '0.0.0.0';

	/**
	 * @var	bool	Whether this is an AJAX request
	 */
	public static $is_ajax = FALSE;

	public static function instance($uri = NULL)
	{
		// Only process the initial request once
		if ( ! Request::$instance)
		{
			// Get the IP address
			Request::$client_ip = Request::real_ip();

			// Lets get the Request method
			isset($_SERVER['REQUEST_METHOD']) AND Request::$method = $_SERVER['REQUEST_METHOD'];
			
			// Check if the protocol is HTTPS
			( ! empty($_SERVER['HTTPS'])) AND Request::$protocol = 'https';

			// Check and see if this is an AJAX request
			if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
			{
				Request::$is_ajax = TRUE;
			}

			// Get the referer
			isset($_SERVER['HTTP_REFERER']) AND Request::$referrer = $_SERVER['HTTP_REFERER'];

			// Get the user agent
			isset($_SERVER['HTTP_USER_AGENT']) AND Request::$user_agent = $_SERVER['HTTP_USER_AGENT'];

			// We manually parse the php input into $_POST if we aren't using GET or POST
			if (Request::$method !== 'GET' AND Request::$method !== 'POST')
			{
				parse_str(file_get_contents('php://input'), $_POST);
			}

			if ($uri === NULL)
			{
				$uri = Request::get_uri();
			}

		}

		Request::$instance = Request::$current = new Request($uri);

		return Request::$instance;
	}

	public static function get_uri()
	{
		// We want to use PATH_INFO if we can
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
				throw new Carbon_Exception('Unable to detect the URI.');
			}

			// Remove the base URL from the URI
			$base_url = parse_url(Config::get('base_url'), PHP_URL_PATH);

			if ($uri != '' AND strpos($uri, $base_url) === 0)
			{
				$uri = substr($uri, strlen($base_url));
			}

			$index_file = Config::get('index_file');

			// If we are using an index file (not mod_rewrite) then remove it
			if ($index_file AND strpos($uri, $index_file) === 0)
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

		// Get rid of double slashes
		$uri = str_replace('//', '/', $uri);

		// Get rid of any dot paths.
		$uri = str_replace('../', '', $uri);

		return $uri;
	}

	/**
	 * Get the real ip address of the user.  Even if they are using a proxy.
	 *
	 * @static
	 * @access	public
	 * @return	string
	 */
	public static function real_ip()
	{
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		elseif (isset($_SERVER['HTTP_CLIENT_IP']))
		{
			return $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (isset($_SERVER['REMOTE_ADDR']))
		{
			return $_SERVER['REMOTE_ADDR'];
		}
	}

	/**
	 * Generates a new request.  This is used for HMVC.
	 *
	 * @access	public
	 * @param	string	The URI of the request
	 * @return	object	The new request
	 */
	public static function factory($uri)
	{
		return new Request($uri);
	}

	/**
	 * @var	string	Holds the response of the request.
	 */
	public $output = NULL;

	/**
	 * @var	string	The Request's URI
	 */
	public $uri = '';

	/**
	 * @var	string	The Request's Routed URI
	 */
	public $routed_uri = '';

	/**
	 * @var	array	The URI segments
	 */
	public $segments = array();

	/**
	 * @var	array	The routed URI segments
	 */
	public $routed_segments = array();

	public $controller = '';
	
	public $action = 'index';

	public function __construct($uri)
	{
		$uri = trim($uri, '/');
		
		$this->uri = $uri;
		$this->segments = explode('/', $uri);
	}

	public function execute()
	{
		// TODO: Write the Route class and parse the routes.
		$this->routed_uri = 'welcome/index';
		$this->routed_segments = explode('/', $this->routed_uri);

		list($controller, $action) = array_pad($this->routed_segments, 2, FALSE);
		
		$controller AND $this->controller = $controller;
		$action AND $this->action = $action;

		$class = 'controller_'.$this->controller;
		$method = 'action_'.$this->action;

		//TODO: Do error checking and implement some sort of 404 handling
		$controller = new $class($this);
		$controller->{$method}();
		return $this;
	}

	public function segment($segment_num)
	{
		if ( ! isset($this->segments[$segment_num - 1]))
		{
			throw new Carbon_Exception('Invalid segment number \''.$segment_num.'\'.');
		}

		return $this->segments[$segment_num - 1];
	}
}

/* End of file request.php */