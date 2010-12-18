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

/**
 * This code is based on Redisent, a Redis interface for the modest.
 *
 * It has been modified to work with Fuel and to improve the code slightly.
 *
 * @author Justin Poliey <jdp34@njit.edu>
 * @copyright 2009 Justin Poliey <jdp34@njit.edu>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

namespace Fuel\Core;

use Fuel\App as App;

/**
 * Redisent, a Redis interface for the modest among us
 */
class Redis {

	protected static $instances = array();

	public static function instance($name = 'default')
	{
		if (\array_key_exists($name, static::$instances))
		{
			return static::$instances[$name];
		}

		if (empty(static::$instances))
		{
			App\Config::load('db', true);
		}
		if ( ! ($config = App\Config::get('db.redis.'.$name)))
		{
			throw new App\Redis_Exception('Invalid instance name given.');
		}

		static::$instances[$name] = new static($config);

		return static::$instances[$name];
	}

	protected $connection = false;

	public function  __construct(array $config = array())
	{
		$this->connection = @fsockopen($config['hostname'], $config['port'], $errno, $errstr);

		if ( ! $this->connection)
		{
			throw new App\Redis_Exception($errstr, $errno);
		}
	}

	public function  __destruct()
	{
		fclose($this->connection);
	}

	public function __call($name, $args)
	{
		$response = null;

		$name = strtoupper($name);

		$command = '*'.(count($args) + 1).CRLF;
		$command .= '$'.strlen($name).CRLF;
		$command .= $name.CRLF;

		foreach ($args as $arg)
		{
			$command .= '$'.strlen($arg).CRLF;
			$command .= $arg.CRLF;
		}

		fwrite($this->connection, $command);

		$reply = trim(fgets($this->connection, 512));

		switch (substr($reply, 0, 1))
		{
			// Error
			case '-':
				throw new App\Redis_Exception(substr(trim($reply), 4));
			break;

			// In-line reply
			case '+':
				$response = substr(trim($reply), 1);
			break;

			// Bulk reply
			case '$':
				if ($reply == '$-1')
				{
					$response = null;
					break;
				}
				$read = 0;
				$size = substr($reply, 1);
				do
				{
					$block_size = ($size - $read) > 1024 ? 1024 : ($size - $read);
					$response .= fread($this->connection, $block_size);
					$read += $block_size;
				} while ($read < $size);
				fread($this->connection, 2);
			break;

			// Mult-Bulk reply
			case '*':
				$count = substr($reply, 1);
				if ($count == '-1')
				{
					return null;
				}
				$response = array();
				for ($i = 0; $i < $count; $i++)
				{
					$bulk_head = trim(fgets($this->connection, 512));
					$size = substr($bulk_head, 1);
					if ($size == '-1')
					{
						$response[] = null;
					}
					else
					{
						$read = 0;
						$block = "";
						do
						{
							$block_size = ($size - $read) > 1024 ? 1024 : ($size - $read);
							$block .= fread($this->connection, $block_size);
							$read += $block_size;
						} while ($read < $size);
						fread($this->connection, 2); /* discard crlf */
						$response[] = $block;
					}
				}
			break;

			// Integer Reply
			case ':':
				$response = substr(trim($reply), 1);
			break;

			// Don't know what to do?  Throw it outta here
			default:
				throw new App\Redis_Exception("invalid server response: {$reply}");
			break;
		}

		return $response;
	}

}
