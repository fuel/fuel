<?php
/**
 * Fuel is a fast, lightweight, community driven PHP 5.4+ framework.
 *
 * @package    Fuel
 * @version    1.8.1
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2018 Fuel Development Team
 * @link       http://fuelphp.com
 */

/**
 * Set error reporting and display errors settings.  You will want to change these when in production.
 */
error_reporting(-1);
ini_set('display_errors', 1);

/**
 * Website document root
 */
define('DOCROOT', __DIR__.DIRECTORY_SEPARATOR);

/**
 * Path to the application directory.
 */
define('APPPATH', realpath(__DIR__.'/../fuel/app/').DIRECTORY_SEPARATOR);

/**
 * Path to the default packages directory.
 */
define('PKGPATH', realpath(__DIR__.'/../fuel/packages/').DIRECTORY_SEPARATOR);

/**
 * The path to the framework core.
 */
define('COREPATH', realpath(__DIR__.'/../fuel/core/').DIRECTORY_SEPARATOR);

// Get the start time and memory for use later
defined('FUEL_START_TIME') or define('FUEL_START_TIME', microtime(true));
defined('FUEL_START_MEM') or define('FUEL_START_MEM', memory_get_usage());

// Load in the Fuel autoloader
if ( ! file_exists(COREPATH.'classes'.DIRECTORY_SEPARATOR.'autoloader.php'))
{
	die('No composer autoloader found. Please run composer to install the FuelPHP framework dependencies first!');
}

// Activate the framework class autoloader
require COREPATH.'classes'.DIRECTORY_SEPARATOR.'autoloader.php';
class_alias('Fuel\\Core\\Autoloader', 'Autoloader');

// Exception route processing closure
$routerequest = function($request = null, $e = false)
{
	Request::reset_request(true);

	$route = array_key_exists($request, Router::$routes) ? Router::$routes[$request]->translation : Config::get('routes.'.$request);

	if ($route instanceof Closure)
	{
		$response = $route();

		if( ! $response instanceof Response)
		{
			$response = Response::forge($response);
		}
	}
	elseif ($e === false)
	{
		$response = Request::forge()->execute()->response();
	}
	elseif ($route)
	{
		$response = Request::forge($route, false)->execute(array($e))->response();
	}
	elseif ($request)
	{
		$response = Request::forge($request)->execute(array($e))->response();
	}
	else
	{
		throw $e;
	}

	return $response;
};

// Generate the request, execute it and send the output.
try
{
	// Boot the app...
	require APPPATH.'bootstrap.php';

	// ... and execute the main request
	$response = $routerequest();
}
catch (HttpBadRequestException $e)
{
	$response = $routerequest('_400_', $e);
}
catch (HttpNoAccessException $e)
{
	$response = $routerequest('_403_', $e);
}
catch (HttpNotFoundException $e)
{
	$response = $routerequest('_404_', $e);
}
catch (HttpServerErrorException $e)
{
	$response = $routerequest('_500_', $e);
}

// This will add the execution time and memory usage to the output.
// Comment this out if you don't use it.
$response->body((string) $response);
if (strpos($response->body(), '{exec_time}') !== false or strpos($response->body(), '{mem_usage}') !== false)
{
	$bm = Profiler::app_total();
	$response->body(
		str_replace(
			array('{exec_time}', '{mem_usage}'),
			array(round($bm[0], 4), round($bm[1] / pow(1024, 2), 3)),
			$response->body()
		)
	);
}

// Send the output to the client
$response->send(true);
