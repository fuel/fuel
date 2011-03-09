<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2011 Fuel Development Team
 * @link       http://fuelphp.com
 */


Autoloader::add_classes(array(
	'Oil\\Command'				   	=> __DIR__.'/classes/command.php',
	'Oil\\Console'					   	=> __DIR__.'/classes/console.php',
	'Oil\\Exception'				   	=> __DIR__.'/classes/exception.php',
	'Oil\\Generate'						=> __DIR__.'/classes/generate.php',
	'Oil\\Generate_Migration_Actions'	=> __DIR__.'/classes/generate/migration/actions.php',
	'Oil\\Package'					 	=> __DIR__.'/classes/package.php',
	'Oil\\Refine'					 	=> __DIR__.'/classes/refine.php',
	'Oil\\Scaffold'					 	=> __DIR__.'/classes/scaffold.php',
));

/* End of file bootstrap.php */