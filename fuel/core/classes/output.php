<?php
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

namespace Fuel\Core;

use Fuel\App as App;

class Output {

	/**
	 * @var	int		The HTTP status code
	 */
	public static $status = 200;

	/**
	 * @var	array	An array of headers
	 */
	public static $headers = array();

	/**
	 * @var	array	An array of status codes and messages
	 */
	public static $statuses = array(
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
	 * Adds a header to the queue
	 *
	 * @access	public
	 * @param	string	The header name
	 * @param	string	The header value
	 * @return	void
	 */
	public static function set_header($name, $value)
	{
		static::$headers[$name] = $value;
	}

	/**
	 * Redirects to another uri/url.  Sets the redirect header,
	 * sends the headers and exits.  Can redirect via a Location header
	 * or using a refresh header.
	 *
	 * The refresh header works better on certain servers like IIS.
	 *
	 * @access	public
	 * @param	string	The url
	 * @param	string	The redirect method
	 * @param	int		The redirect status code
	 * @return	void
	 */
	public static function redirect($url = '', $method = 'location', $redirect_code = 302)
	{
		static::$status = $redirect_code;

		if (strpos($url, '://') === false)
		{
			$url = Uri::create($url);
		}

		if ($method == 'location')
		{
			static::set_header('Location', $url);
		}
		elseif ($method == 'refresh')
		{
			static::set_header('Refresh', '0;url='.$url);
		}
		else
		{
			return;
		}

		static::send_headers();
		exit;
	}

	/**
	 * Sends the headers if they haven't already been sent.
	 *
	 * @access	public
	 * @return	void
	 */
	public static function send_headers()
	{
		if ( ! headers_sent())
		{
			// Send the protocol line first
			$protocol = App\Input::server('SERVER_PROTOCOL') ? App\Input::server('SERVER_PROTOCOL') : 'HTTP/1.1';
			header($protocol.' '.static::$status.' '.static::$statuses[static::$status]);

			foreach (static::$headers as $name => $value)
			{
				is_string($name) and $value = "{$name}: {$value}";
				header($value, true);
			}
		}
	}
}

/* End of file output.php */
