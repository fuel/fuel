<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Thrust
 *
 * Thrust is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Thrust
 * @version		1.0
 * @author		Dan Horrigan <http://dhorrigan.com>
 * @license		Apache License v2.0
 * @copyright	2010 Dan Horrigan
 */

class Thrust_Request {

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
	 * @var	object	Holds the global request instance
	 */
	public static $active = FALSE;

	/**
	 * Returns the a Request object singleton
	 *
	 * @static
	 * @access	public
	 * @return	object
	 */
	public static function instance($uri = NULL)
	{
		if ( ! Request::$instance)
		{
			Request::$instance = Request::$active = new Request($uri);
		}

		return Request::$instance;
	}

	/**
	 * Returns the active request
	 *
	 * @static
	 * @access	public
	 * @return	object
	 */
	public static function active()
	{
		return Request::$active;
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
	 * @var	object	The request's URI object
	 */
	public $uri = '';

	/**
	 * @var	string	The request's controller
	 */
	public $controller = '';

	/**
	 * @var	string	The request's action
	 */
	public $action = 'index';

	public function __construct($uri)
	{
		$this->uri = new URI($uri);
	}

	public function execute()
	{
		// TODO: Write the Route class and parse the routes.
		list($controller, $action) = array_pad($this->uri->segments, 2, FALSE);
		
		$controller AND $this->controller = $controller;
		$action AND $this->action = $action;

		$class = $this->controller.'_controller';
		$method = 'action_'.$this->action;

		//TODO: Do error checking and implement some sort of 404 handling
		$controller = new $class($this);
		$controller->{$method}();
		return $this;
	}

}

/* End of file request.php */