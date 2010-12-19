<?php

namespace Fuel\Core;

use Fuel\App as App;

class Cli {

	public static $wait_msg = 'Press any key to continue...';

	protected static $args = array();

	protected static $foreground_colors = array(
		'black'			=> '0;30',
		'dark_gray'		=> '1;30',
		'blue'			=> '0;34',
		'light_blue'	=> '1;34',
		'green'			=> '0;32',
		'light_green'	=> '1;32',
		'cyan'			=> '0;36',
		'light_cyan'	=> '1;36',
		'red'			=> '0;31',
		'light_red'		=> '1;31',
		'purple'		=> '0;35',
		'light_purple'	=> '1;35',
		'brown'			=> '0;33',
		'yellow'		=> '1;33',
		'light_gray'	=> '0;37',
		'white'			=> '1;37',
	);

	protected static $background_colors = array(
		'black'			=> '40',
		'red'			=> '41',
		'green'			=> '42',
		'yellow'		=> '43',
		'blue'			=> '44',
		'magenta'		=> '45',
		'cyan'			=> '46',
		'light_gray'	=> '47',
	);

	/**
	 * Static constructor.	Parses all the CLI params.
	 */
	public static function _init()
	{
		for ($i = 1; $i < $_SERVER['argc']; $i++)
		{
			$arg = explode('=', $_SERVER['argv'][$i]);

			static::$args[$i] = $arg[0];

			if (count($arg) > 1 || strncmp($arg[0], '-', 1) === 0)
			{
				static::$args[ltrim($arg[0], '-')] = isset($arg[1]) ? $arg[1] : true;
			}
		}
	}

	/**
	 * Returns the option with the given name.	You can also give the option
	 * number.
	 *
	 * Named options must be in the following formats:
	 * php index.php user -v --v -name=John --name=John
	 *
	 * @param	string|int	$name	the name of the option (int if unnamed)
	 * @return	string
	 */
	public static function option($name, $default = null)
	{
		if ( ! isset(static::$args[$name]))
		{
			return $default;
		}
		return static::$args[$name];
	}

	/**
	 * Reads input from the user.  This can have either 1 or 2 arguments.
	 *
	 * Usage:
	 *
	 * // Waits for any key press
	 * CLI::read();
	 *
	 * // Takes any input
	 * $color = CLI::read('What is your favorite color?');
	 *
	 * // Will only accept the options in the array
	 * $ready = CLI::read('Are you ready?', array('y','n'));
	 *
	 * @return	string	the user input
	 */
	public static function read()
	{
		$args = func_get_args();

		// Ask question with options
		if (count($args) == 2)
		{
			list($output, $options)=$args;
		}

		// No question (probably been asked already) so just show options
		elseif (count($args) == 1 && is_array($args[0]))
		{
			$output = '';
			$options = $args[0];
		}

		// Question without options
		elseif (count($args) == 1 && is_string($args[0]))
		{
			$output = $args[0];
			$options = array();
		}

		// Run out of ideas, EPIC FAIL!
		else
		{
			$output = '';
			$options = array();
		}

		// If a question has been asked with the read
		if( ! empty($output))
		{
			$options_output = '';
			if( ! empty($options))
			{
				$options_output = ' [ '.implode(', ', $options).' ]';
			}

			fwrite(STDOUT, $output.$options_output.': ');
		}

		// Read the input from keyboard.
		$input = trim(fgets(STDIN));

		// If options are provided and the choice is not in the array, tell them to try again
		if( ! empty($options) && ! in_array($input, $options))
		{
			static::write("This is not a valid option. Please try again.\n");

			$input = static::read($output, $options);
		}

		// Read the input
		return $input;
	}

	/**
	 * Outputs a string to the cli.	 If you send an array it will implode them
	 * with a line break.
	 *
	 * @param	string|array	$text	the text to output, or array of lines
	 */
	public static function write($text = '')
	{
		if (is_array($text))
		{
			$text = implode(PHP_EOL, $text);
		}
		fwrite(STDOUT, $text.PHP_EOL);
	}

	/**
	 * Beeps a certain number of times.
	 *
	 * @param	int $num	the number of times to beep
	 */
	public static function beep($num = 1)
	{
		echo str_repeat("\x07", $num);
	}

	/**
	 * Waits a certain number of seconds, optionally showing a wait message and
	 * waiting for a key press.
	 *
	 * @param	int		$seconds	number of seconds
	 * @param	bool	$countdown	show a countdown or not
	 */
	public static function wait($seconds = 0, $countdown = false)
	{
		if ( ! $countdown)
		{
			if ($seconds > 0)
			{
				sleep($seconds);
			}
			else
			{
				static::write(static::$wait_msg);
				static::read();
			}
		}
		else
		{
			$time = $seconds;

			while ($time > 0)
			{
				fwrite(STDOUT, $time.'...');
				sleep(1);
				$time--;
			}
			static::write();
		}
	}

	/**
	 * Returns the given text with the correct color codes for a foreground and
	 * optionally a background color.
	 *
	 * @param	string	$text		the text to color
	 * @param	atring	$foreground the foreground color
	 * @param	string	$background the background color
	 * @return	string	the color coded string
	 */
	public static function color($text, $foreground, $background = null)
	{
		if ( ! array_key_exists($foreground, static::$foreground_colors))
		{
			throw new App\Exception('Invalid CLI foreground color: '.$foreground);
		}

		if ( $background !== null and ! array_key_exists($background, static::$background_colors))
		{
			throw new App\Exception('Invalid CLI background color: '.$background);
		}

		$string = "\033[".static::$foreground_colors[$foreground]."m";

		if ($background !== null)
		{
			$string .= "\033[".static::$background_colors[$background]."m";
		}

		$string .= $text."\033[0m";

		return $string;
	}

}

/* End of file cli.php */
