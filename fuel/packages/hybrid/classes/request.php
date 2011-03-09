<?php

namespace Hybrid;

class Request extends \Fuel\Core\Request {
	/**
	 * Generates a new request.  The request is then set to be the active
	 * request.  If this is the first request, then save that as the main
	 * request for the app.
	 *
	 * Usage:
	 *
	 * <code>\Hybrid\Request::connector('GET controller/method?hello=world');</code>
	 *
	 * @access	public
	 * @param	string	The URI of the request
	 * @param	bool	if true use routes to process the URI
	 * @return	object	The new request
	 */
	public static function connector($uri, $dataset = array ()) {
		$uri_segments = explode(' ', $uri);
		$type = 'GET';

		if (in_array(strtoupper($uri_segments[0]), array('DELETE', 'POST', 'PUT', 'GET'))) {
			$uri = $uri_segments[1];
			$type = $uri_segments[0];
		}
		
		$query_string = parse_url($uri, \PHP_URI_QUERY);
		parse_str($query_string, $query_dataset);
		
		$dataset = array_merge($query_dataset, $dataset);
		

		logger(Fuel::L_INFO, 'Creating a new Request with URI = "' . $uri . '"', __METHOD__);

		static::$active = new static($uri, true, $type, $dataset);

		if (!static::$main) {
			logger(Fuel::L_INFO, 'Setting main Request', __METHOD__);
			static::$main = static::$active;
		}

		return static::$active;
	}
	
	private static $_request_data = array();
	private static $_request_method = '';

	/**
	 * Create a request object
	 * 
	 * @param string $uri
	 * @param boolean $route
	 * @param string $type GET|POST|PUT|DELETE
	 * @param array $dataset 
	 */
	public function __construct($uri, $route, $type = 'GET', $dataset = array()) {
		parent::__construct($uri, $route);

		static::$_request_method = $type;
		static::$_request_data = $dataset;
	}
	
	/**
	 * Cleaning up our request after executing \Request::execute()
	 * 
	 * Usage:
	 * 
	 * <code>list($data, $status) = \Hybrid\Request::connector('PUT controller/model?hello=world')->execute();</code>
	 * 
	 * @return array containing $data and HTTP Response $status
	 * @see \Request::execute()
	 */
	public function execute() {
		\Hybrid\Input::connect(static::$_request_method, static::$_request_data);
		
		$status = \Output::$status;

		$execute = parent::execute();

		static::$_request_method = '';
		static::$_request_data = array();

		$execute_status = \Output::$status;
		\Output::$status = $status;
		
		\Hybrid\Input::disconnect();

		return array($execute, $execute_status);
	}
	
	
}