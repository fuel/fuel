<?php

namespace Workflow;

class Input {
	public static function __callStatic($name, $args) {
		$request = \Workflow\Request::connection();
		
		$using_connector = ($request->method !== '' ? true : false);
		
		$default = null;
		$index = null;
		
		switch (true) {
			case count($args) > 1 :
				$default = $args[1];
			case count($args) > 0 :
				$index = $args[0];
				break;
		}

		/*
		 * What if the request meant for other than get, post, get_post, put and delete?
		 */
		switch ($name = strtolower($name)) {
			case 'method' :
				return (true === $using_connector ? strtoupper($request->method) : call_user_func(array('\\Input', 'method')));
				break;
			case 'is_ajax' :
			case 'user_agent' :
			case 'real_ip' :
			case 'referrer' :
				return call_user_func(array('\\Input', $name));
				break;
			case 'server' :
				return call_user_func_array(array('\\Input', $name), $args);
				break;
		}

		// Reach this point but $index is null (which isn't be so we should just return the default value)
		if (is_null($index)) {
			return $default;
		}

		if (strtoupper($name) === $request->method && true === $using_connector) {
			return isset($request->data[$index]) ? $request->data[$index] : $default;
		} else {
			$name = strtolower($name);

			if (method_exists('\\Input', $name)) {
				return call_user_func_array(array('\\Input', $name), $args);
			} else {
				return $default;
			}
		}
		
	}
}
