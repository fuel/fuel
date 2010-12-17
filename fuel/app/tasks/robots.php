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
 * Robot example task
 *
 * Ruthlessly stolen from the beareded Canadian sexy symbol:
 *
 *		Derek Allard: http://derekallard.com/
 *
 * @package		Fuel
 * @version		1.0
 * @author		Phil Sturgeon
 */

namespace Fuel\Tasks;

use Fuel\App as App;

class Robots {

	/**
	 * This method gets ran when a valid method name is not used in the command.
	 *
	 * Usage (from command line):
	 *
	 * php oil r robots
	 *
	 * or
	 *
	 * php oil r robots "Kill all Mice"
	 *
	 * @return string
	 */
	public function run($speech = null)
	{
		if ( ! isset($speech))
		{
			$speech = 'KILL ALL HUMANS!';
		}

		$eye = App\Cli::color("*", 'red');

		return App\Cli::color("
					\"{$speech}\"
			          _____     /
			         /_____\\", 'blue')."\n"
.App\Cli::color("			    ____[\\", 'blue').$eye.App\Cli::color('---', 'blue').$eye.App\Cli::color('/]____', 'blue')."\n"
.App\Cli::color("			   /\\ #\\ \\_____/ /# /\\
			  /  \\# \\_.---._/ #/  \\
			 /   /|\\  |   |  /|\\   \\
			/___/ | | |   | | | \\___\\
			|  |  | | |---| | |  |  |
			|__|  \\_| |_#_| |_/  |__|
			//\\\\  <\\ _//^\\\\_ />  //\\\\
			\\||/  |\\//// \\\\\\\\/|  \\||/
			      |   |   |   |
			      |---|   |---|
			      |---|   |---|
			      |   |   |   |
			      |___|   |___|
			      /   \\   /   \\
			     |_____| |_____|
			     |HHHHH| |HHHHH|", 'blue');
	}

	/**
	 * An example method that is here just to show the various uses of tasks.
	 *
	 * Usage (from command line):
	 *
	 * php oil r robots protect
	 *
	 * @return string
	 */
	public function protect()
	{
		$eye = App\Cli::color("*", 'green');

		return App\Cli::color("
					\"PROTECT ALL HUMANS\"
			          _____     /
			         /_____\\", 'blue')."\n"
.App\Cli::color("			    ____[\\", 'blue').$eye.App\Cli::color('---', 'blue').$eye.App\Cli::color('/]____', 'blue')."\n"
.App\Cli::color("			   /\\ #\\ \\_____/ /# /\\
			  /  \\# \\_.---._/ #/  \\
			 /   /|\\  |   |  /|\\   \\
			/___/ | | |   | | | \\___\\
			|  |  | | |---| | |  |  |
			|__|  \\_| |_#_| |_/  |__|
			//\\\\  <\\ _//^\\\\_ />  //\\\\
			\\||/  |\\//// \\\\\\\\/|  \\||/
			      |   |   |   |
			      |---|   |---|
			      |---|   |---|
			      |   |   |   |
			      |___|   |___|
			      /   \\   /   \\
			     |_____| |_____|
			     |HHHHH| |HHHHH|", 'blue');

	}
}

/* End of file tasks/migrate.php */