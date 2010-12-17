<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Phil Sturgeon
 * @license		MIT License
 * @copyright	2010 Dan Horrigan
 * @link		http://fuelphp.com
 */

namespace Oil;

use Fuel\App;

class Console {

	public function __construct()
	{
		error_reporting(E_ALL | E_STRICT);

		ini_set("error_log", NULL);
		ini_set("log_errors", 1);
		ini_set("html_errors", 0);
		ini_set("display_errors", 0);

		while (ob_get_level ())
		{
			ob_end_clean();
		}

		ob_implicit_flush(true);

		// And, go!
		self::main();
	}

	private function main()
	{
		echo sprintf(
			'Fuel %s - PHP %s (%s) (%s) [%s]',
			\Fuel::VERSION,
			phpversion(),
			php_sapi_name(),
			self::build_date(),
			PHP_OS
		) . PHP_EOL;

		// Loop until they break it
		while (TRUE)
		{
			echo ">>> ";

			if (!$__line = trim(fgets(STDIN), PHP_EOL))
			{
				continue;
			}

			if ($__line == 'quit')
			{
				break;
			}

			if (self::is_immediate($__line))
			{
				$__line = "return ($__line)";
			}

			ob_start();
			$ret = eval("unset(\$__line); $__line;");

			if (ob_get_length() == 0)
			{
				if (is_bool($ret))
				{
					echo ($ret ? "true" : "false");
				}
				else if (is_string($ret))
				{
					echo addcslashes($ret, "\0..\37\177..\377");
				}
				else if (!is_null($ret))
				{
					var_export($ret);
				}
			}

			unset($ret);
			$out = ob_get_contents();
			ob_end_clean();

			if ((strlen($out) > 0) && (substr($out, -1) != PHP_EOL))
			{
				$out .= PHP_EOL;
			}

			echo $out;
			unset($out);
		}
	}

	private function is_immediate($line)
	{
		$skip = array(
			'class', 'declare', 'die', 'echo', 'exit', 'for',
			'foreach', 'function', 'global', 'if', 'include',
			'include_once', 'print', 'require', 'require_once',
			'return', 'static', 'switch', 'unset', 'while'
		);

		$okeq = array('===', '!==', '==', '!=', '<=', '>=');

		$code = '';
		$sq = false;
		$dq = false;

		for ($i = 0; $i < strlen($line); $i++)
		{
			$c = $line{$i};
			if ($c == "'")
			{
				$sq = !$sq;
			}
			else if ($c == '"')
			{
				$dq = !$dq;
			}

			else if ( ($sq) || ($dq) && $c == "\\")
			{
				++$i;
			}
			else
			{
				$code .= $c;
			}
		}

		$code = str_replace($okeq, '', $code);
		if (strcspn($code, ';{=') != strlen($code))
		{
			return false;
		}

		$kw = preg_split("[^A-Za-z0-9_]", $code);
		foreach ($kw as $i)
		{
			if (in_array($i, $skip))
			{
				return false;
			}
		}

		return true;
	}

	private function build_date()
	{
		ob_start();
		phpinfo(INFO_GENERAL);

		$x = ob_get_contents();
		ob_end_clean();

		$x = strip_tags($x);
		$x = explode(PHP_EOL, $x);
		$s = array('Build Date => ', 'Build Date ');

		foreach ($x as $i)
		{
			foreach ($s as $j)
			{
				if (substr($i, 0, strlen($j)) == $j)
				{
					return trim(substr($i, strlen($j)));
				}
			}
		}

		return '???';
	}

}