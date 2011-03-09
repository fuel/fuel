<?php

namespace Hybrid;

class Input {
	
	protected static $request = null;
	
	public static function connect($method = '', $data = array ()) {
		if (!empty($method)) {
			static::$request = (object) array('method' => $method, 'data' => $data);
		}
	}
	
	public static function disconnect() {
		static::$request = null;
	}
	
	public static function __callStatic($name, $args) {
		// If $request is null, it's a request from \Fuel\Core\Request so all call to 
		if (is_null(static::$request) || in_array($name, array('is_ajax', 'user_agent', 'real_ip', 'referrer', 'server'))) {
			return call_user_func(array('\\Input', $name), $args);
		}
		
		$using_connector = ($request->method !== '' ? true : false);
		
		if (false === $using_connector) {
			return call_user_func(array('\\Input', $name), $args);
		}
		
		$default = null;
		$index = null;
		
		switch (true) {
			case count($args) > 1 :
				$default = $args[1];
			case count($args) > 0 :
				$index = $args[0];
				break;
		}
		
		if ($name === 'method') {
			return $request->method;
		}
		
		// Reach this point but $index is null (which isn't be so we should just return the default value)
		if (is_null($index)) {
			return $default;
		}

		if (strtoupper($name) === $request->method && true === $using_connector) {
			return isset($request->data[$index]) ? $request->data[$index] : $default;
		} else {
			return $default;
		}
		
	}
}
