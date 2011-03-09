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
	 * Creates the new Request object by getting a new URI object, then parsing
     * the uri with the Route class. Once constructed we need to save the method 
	 * and GET/POST/PUT or DELETE dataset
	 * 
	 * @param string $uri
	 * @param boolean $route
	 * @param string $type GET|POST|PUT|DELETE
	 * @param array $dataset 
	 */
	public function __construct($uri, $route, $type = 'GET', $dataset = array()) {
		parent::__construct($uri, $route);

		/* store this construct method and data static-ly */
		static::$_request_method = $type;
		static::$_request_data = $dataset;
	}
	
	/**
	 * This executes the request and sets the output to be used later. 
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
		/* Since this just a imitation of curl request, \Hybrid\Input need to know the 
		 * request method and data available in the connection. 
		 */
		\Hybrid\Input::connect(static::$_request_method, static::$_request_data);
		
		/* Keep a copy or current HTTP Response status */
		$original_status = \Output::$status;

		$execute_object = parent::execute();

		$execute_status = \Output::$status;
		
		/* Revert HTTP Response status to that value before this connection.
		 * Sub-request response status shouldn't affect the original request.
		 */
		\Output::$status = $original_status;
		
		/* We need to clean-up any request object transfered to \Hybrid\Input so that
		 * any following request to \Hybrid\Input will redirected to \Fuel\Core\Input
		 */
		\Hybrid\Input::disconnect();
		static::$_request_method = '';
		static::$_request_data = array();

		return array($execute_object, $execute_status);
	}
	
	
}