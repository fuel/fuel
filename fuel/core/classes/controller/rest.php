<?php

namespace Fuel\Core;

abstract class Controller_Rest extends \Controller {

	protected $rest_format = null; // Set this in a controller to use a default format
	protected $methods = array(); // contains a list of method properties such as limit, log and level

	// List all supported methods, the first will be the default format
	protected $_supported_formats = array(
		'xml' => 'application/xml',
		'rawxml' => 'application/xml',
		'json' => 'application/json',
		'serialize' => 'application/vnd.php.serialized',
		'php' => 'text/plain',
		'html' => 'text/html',
		'csv' => 'application/csv'
	);

	public function before()
	{
		parent::before();

		\Config::load('rest', true);

		if (\Config::get('rest.auth') == 'basic')
		{
			$this->_prepare_basic_auth();
		}
		elseif (\Config::get('rest.auth') == 'digest')
		{
			$this->_prepare_digest_auth();
		}

		// Some Methods cant have a body
		$this->request->body = null;

		// Which format should the data be returned in?
		$this->request->lang = $this->_detect_lang();
	}

	/*
	 * Remap
	 *
	 * Requests are not made to methods directly The request will be for an "object".
	 * this simply maps the object and method to the correct Controller method.
	 */

	public function router($resource, $arguments)
	{
		$pattern = '/\.(' . implode('|', array_keys($this->_supported_formats)) . ')$/';

		// Check if a file extension is used
		if (preg_match($pattern, $resource, $matches))
		{
			// Remove the extension from arguments too
			$resource = preg_replace($pattern, '', $resource);

			$this->request->format = $matches[1];
		}
		else
		{
			// Which format should the data be returned in?
			$this->request->format = $this->_detect_format();
		}

		// If they call user, go to $this->post_user();
		$controller_method = strtolower(\Input::method()) . '_' . $resource;

		call_user_func(array($this, $controller_method));
	}

	/*
	 * response
	 *
	 * Takes pure data and optionally a status code, then creates the response
	 */

	protected function response($data = array(), $http_code = 200)
	{
		if (empty($data))
		{
			$this->response->status = 404;
			return;
		}

		$this->response->status = $http_code;

		// If the format method exists, call and return the output in that format
		if (method_exists('Controller_Rest', '_format_' . $this->request->format))
		{
			// Set the correct format header
			$this->response->set_header('Content-Type', $this->_supported_formats[$this->request->format]);

			$this->response->body($this->{'_format_' . $this->request->format}($data));
		}

		// Format not supported, output directly
		else
		{
			$this->response->body((string) $data);
		}
	}

	/*
	 * Detect format
	 *
	 * Detect which format should be used to output the data
	 */

	private function _detect_format()
	{
		// A format has been passed as an argument in the URL and it is supported
		if (\Input::get_post('format') and $this->_supported_formats[\Input::get_post('format')])
		{
			return \Input::get_post('format');
		}

		// Otherwise, check the HTTP_ACCEPT (if it exists and we are allowed)
		if (\Config::get('rest.ignore_http_accept') === false and \Input::server('HTTP_ACCEPT'))
		{
			// Check all formats against the HTTP_ACCEPT header
			foreach (array_keys($this->_supported_formats) as $format)
			{
				// Has this format been requested?
				if (strpos(\Input::server('HTTP_ACCEPT'), $format) !== false)
				{
					// If not HTML or XML assume its right and send it on its way
					if ($format != 'html' and $format != 'xml')
					{
						return $format;
					}

					// HTML or XML have shown up as a match
					else
					{
						// If it is truely HTML, it wont want any XML
						if ($format == 'html' and strpos(\Input::server('HTTP_ACCEPT'), 'xml') === false)
						{
							return $format;
						}

						// If it is truely XML, it wont want any HTML
						elseif ($format == 'xml' and strpos(\Input::server('HTTP_ACCEPT'), 'html') === false)
						{
							return $format;
						}
					}
				}
			}
		} // End HTTP_ACCEPT checking
		// Well, none of that has worked! Let's see if the controller has a default
		if (!empty($this->rest_format))
		{
			return $this->rest_format;
		}

		// Just use the default format
		return \Config::get('rest.default_format');
	}

	/*
	 * Detect language(s)
	 *
	 * What language do they want it in?
	 */

	private function _detect_lang()
	{
		if (!$lang = \Input::server('HTTP_ACCEPT_LANGUAGE'))
		{
			return null;
		}

		// They might have sent a few, make it an array
		if (strpos($lang, ',') !== false)
		{
			$langs = explode(',', $lang);

			$return_langs = array();
			$i = 1;
			foreach ($langs as $lang)
			{
				// Remove weight and strip space
				list($lang) = explode(';', $lang);
				$return_langs[] = trim($lang);
			}

			return $return_langs;
		}

		// Nope, just return the string
		return $lang;
	}

	// SECURITY FUNCTIONS ---------------------------------------------------------

	private function _check_login($username = '', $password = null)
	{
		if (empty($username))
		{
			return false;
		}

		$valid_logins = & \Config::get('rest.valid_logins');

		if (!array_key_exists($username, $valid_logins))
		{
			return false;
		}

		// If actually null (not empty string) then do not check it
		if ($password !== null and $valid_logins[$username] != $password)
		{
			return false;
		}

		return true;
	}

	private function _prepare_basic_auth()
	{
		$username = null;
		$password = null;

		// mod_php
		if (\Input::server('PHP_AUTH_USER'))
		{
			$username = \Input::server('PHP_AUTH_USER');
			$password = \Input::server('PHP_AUTH_PW');
		}

		// most other servers
		elseif (\Input::server('HTTP_AUTHENTICATION'))
		{
			if (strpos(strtolower(\Input::server('HTTP_AUTHENTICATION')), 'basic') === 0)
			{
				list($username, $password) = explode(':', base64_decode(substr(\Input::server('HTTP_AUTHORIZATION'), 6)));
			}
		}

		if (!self::_check_login($username, $password))
		{
			self::_force_login();
		}
	}

	private function _prepare_digest_auth()
	{
		$uniqid = uniqid(""); // Empty argument for backward compatibility
		// We need to test which server authentication variable to use
		// because the PHP ISAPI module in IIS acts different from CGI
		if (\Input::server('PHP_AUTH_DIGEST'))
		{
			$digest_string = \Input::server('PHP_AUTH_DIGEST');
		}
		elseif (\Input::server('HTTP_AUTHORIZATION'))
		{
			$digest_string = \Input::server('HTTP_AUTHORIZATION');
		}
		else
		{
			$digest_string = "";
		}

		/* The $_SESSION['error_prompted'] variabile is used to ask
		  the password again if none given or if the user enters
		  a wrong auth. informations. */
		if (empty($digest_string))
		{
			self::_force_login($uniqid);
		}

		// We need to retrieve authentication informations from the $auth_data variable
		preg_match_all('@(username|nonce|uri|nc|cnonce|qop|response)=[\'"]?([^\'",]+)@', $digest_string, $matches);
		$digest = array_combine($matches[1], $matches[2]);

		if (!array_key_exists('username', $digest) or !self::_check_login($digest['username']))
		{
			self::_force_login($uniqid);
		}

		$valid_logins = & \Config::get('rest.valid_logins');
		$valid_pass = $valid_logins[$digest['username']];

		// This is the valid response expected
		$A1 = md5($digest['username'] . ':' . \Config::get('rest.realm') . ':' . $valid_pass);
		$A2 = md5(strtoupper(\Input::method()) . ':' . $digest['uri']);
		$valid_response = md5($A1 . ':' . $digest['nonce'] . ':' . $digest['nc'] . ':' . $digest['cnonce'] . ':' . $digest['qop'] . ':' . $A2);

		if ($digest['response'] != $valid_response)
		{
			header('HTTP/1.0 401 Unauthorized');
			header('HTTP/1.1 401 Unauthorized');
			exit;
		}
	}

	private function _force_login($nonce = '')
	{
		header('HTTP/1.0 401 Unauthorized');
		header('HTTP/1.1 401 Unauthorized');

		if (\Config::get('rest.auth') == 'basic')
		{
			header('WWW-Authenticate: Basic realm="' . \Config::get('rest.realm') . '"');
		}
		elseif (\Config::get('rest.auth') == 'digest')
		{
			header('WWW-Authenticate: Digest realm="' . \Config::get('rest.realm') . '" qop="auth" nonce="' . $nonce . '" opaque="' . md5(\Config::get('rest.realm')) . '"');
		}

		exit('Not authorized.');
	}

	// Force it into an array
	private function _force_loopable($data)
	{
		// Force it to be something useful
		if (!is_array($data) and !is_object($data))
		{
			$data = (array) $data;
		}

		return $data;
	}

	// FORMATING FUNCTIONS ---------------------------------------------------------
	// Format XML for output
	private function _format_xml($data = array(), $structure = null, $basenode = 'xml')
	{
		// turn off compatibility mode as simple xml throws a wobbly if you don't.
		if (ini_get('zend.ze1_compatibility_mode') == 1)
		{
			ini_set('zend.ze1_compatibility_mode', 0);
		}

		if ($structure == null)
		{
			$structure = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$basenode />");
		}

		// loop through the data passed in.
		$data = self::_force_loopable($data);
		foreach ($data as $key => $value)
		{
			// no numeric keys in our xml please!
			if (is_numeric($key))
			{
				// make string key...
				//$key = "item_". (string) $key;
				$key = "item";
			}

			// replace anything not alpha numeric
			$key = preg_replace('/[^a-z_]/i', '', $key);

			// if there is another array found recrusively call this function
			if (is_array($value) or is_object($value))
			{
				$node = $structure->addChild($key);
				// recrusive call.
				self:: _format_xml($value, $node, $basenode);
			}
			else
			{
				// Actual boolean values need to be converted to numbers
				is_bool($value) and $value = (int) $value;

				// add single node.
				$value = htmlentities($value, ENT_NOQUOTES, "UTF-8");

				$UsedKeys[] = $key;

				$structure->addChild($key, $value);
			}
		}

		// pass back as string. or simple xml object if you want!
		return $structure->asXML();
	}

	// Format Raw XML for output
	private function _format_rawxml($data = array(), $structure = null, $basenode = 'xml')
	{
		// turn off compatibility mode as simple xml throws a wobbly if you don't.
		if (ini_get('zend.ze1_compatibility_mode') == 1)
		{
			ini_set('zend.ze1_compatibility_mode', 0);
		}

		if ($structure == null)
		{
			$structure = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$basenode />");
		}

		// loop through the data passed in.
		$data = self::_force_loopable($data);
		foreach ($data as $key => $value)
		{
			// no numeric keys in our xml please!
			if (is_numeric($key))
			{
				// make string key...
				//$key = "item_". (string) $key;
				$key = "item";
			}

			// replace anything not alpha numeric
			$key = preg_replace('/[^a-z0-9_-]/i', '', $key);

			// if there is another array found recrusively call this function
			if (is_array($value) or is_object($value))
			{
				$node = $structure->addChild($key);
				// recrusive call.
				self::_format_rawxml($value, $node, $basenode);
			}
			else
			{
				// Actual boolean values need to be converted to numbers
				is_bool($value) and $value = (int) $value;

				// add single node.
				$value = htmlentities($value, ENT_NOQUOTES, "UTF-8");

				$UsedKeys[] = $key;

				$structure->addChild($key, $value);
			}
		}

		// pass back as string. or simple xml object if you want!
		return $structure->asXML();
	}

	// Format HTML for output
//	private function _format_html($data = array())
//	{
//		// Multi-dimentional array
//		if (isset($data[0]))
//		{
//			$headings = array_keys($data[0]);
//		}
//
//		// Single array
//		else
//		{
//			$headings = array_keys($data);
//			$data = array($data);
//		}
//
//		self::load->library('table');
//
//		self::table->set_heading($headings);
//
//		foreach($data as &$row)
//		{
//			self::table->add_row($row);
//		}
//
//		return self::table->generate();
//	}
	// Format HTML for output
	private function _format_csv($data = array())
	{
		// Multi-dimentional array
		if (isset($data[0]))
		{
			$headings = array_keys($data[0]);
		}

		// Single array
		else
		{
			$headings = array_keys($data);
			$data = array($data);
		}

		$output = implode(',', $headings) . "\r\n";
		foreach ($data as &$row)
		{
			$output .= '"' . implode('","', $row) . "\"\r\n";
		}

		return $output;
	}

	// Encode as JSON
	private function _format_json($data = array())
	{
		return json_encode($data);
	}

	// Encode as Serialized array
	private function _format_serialize($data = array())
	{
		return serialize($data);
	}

	// Encode raw PHP
	private function _format_php($data = array())
	{
		return var_export($data, true);
	}

}

/* End of file rest.php */