<?php

class Connector extends \Request {

	/**
	 * Generates a new request.  The request is then set to be the active
	 * request.  If this is the first request, then save that as the main
	 * request for the app.
	 *
	 * Usage:
	 *
	 * <code>Connector::factory('GET hello/world');</code>
	 *
	 * @access	public
	 * @param	string	The URI of the request
	 * @param	bool	if true use routes to process the URI
	 * @return	object	The new request
	 */
	public static function factory($uri, $dataset, $route = true) {
		$uri_segments = explode(' ', $uri);
		$type = 'GET';

		if (in_array(strtoupper($uri_segments[0]), array('DELETE', 'POST', 'PUT', 'GET'))) {
			$uri = $uri_segments[1];
			$type = $uri_segments[0];
		}

		logger(Fuel::L_INFO, 'Creating a new Request with URI = "' . $uri . '"', __METHOD__);

		static::$active = new static($uri, $route, $type, $dataset);

		if (!static::$main) {
			logger(Fuel::L_INFO, 'Setting main Request', __METHOD__);
			static::$main = static::$active;
		}

		return static::$active;
	}

	private static $_request_data = array();
	private static $_request_method = '';

	public function __construct($uri, $route, $type = 'GET', $dataset = array()) {
		parent::__construct($uri, $route);

		static::$_request_method = $type;
		static::$_request_data = $dataset;
	}

	/**
	 * 
	 * @param string $type
	 * @param type $index
	 * @param type $default
	 * @return type 
	 */
	public static function input($type, $index = null, $default = null) {
		$using_connector = (static::$_request_method !== '' ? true : false);

		/*
		 * What if the request meant for other than get, post, get_post, put and delete?
		 */
		switch (strtolower($type)) {
			case 'method' :
				return (true === $using_connector ? strtoupper(static::$_request_method) : \Input::method());
				break;
			case 'is_ajax' :
			case 'user_agent' :
			case 'real_ip' :
			case 'referrer' :
				return call_user_func_array(array('\\Input', strtolower($type)));
				break;
		}

		// Reach this point but $index is null (which isn't be so we should just return the default value)
		if (is_null($index)) {
			return $default;
		}

		if (strtoupper($type) === static::$_request_method && isset(static::$_request_data[$index])) {
			return static::$_request_data[$index];
		} else {
			$type = strtolower($type);

			if (method_exists('\\Input', $type)) {
				return call_user_func_array(array('\\Input', $type), array($index, $default));
			} else {
				return $default;
			}
		}
	}

	/**
	 * Cleaning up our request after executing \Request::execute()
	 * 
	 * Usage:
	 * 
	 * <code>list($data, $status) = Connector::factory('GET controller/model', array('hello' => 'world'))->execute();</code>
	 * 
	 * @return array containing $data and request $status
	 * @see \Request::execute()
	 */
	public function execute() {
		$status = \Output::$status;

		$execute = parent::execute();

		static::$_request_method = '';
		static::$_request_data = array();

		$execute_status = \Output::$status;
		\Output::$status = $status;

		return array($execute, $execute_status);
	}

}