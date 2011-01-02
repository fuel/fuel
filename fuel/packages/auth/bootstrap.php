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
 * @copyright	2010 - 2011 Fuel Development Team
 * @link		http://fuelphp.com
 */



Fuel\Core\Autoloader::add_classes(array(
	'Auth\\Auth'					=> __DIR__.'/classes/auth.php',
	'Auth\\Auth_Driver'				=> __DIR__.'/classes/auth/driver.php',
	'Auth\\Auth_Exception'			=> __DIR__.'/classes/auth/exception.php',
	'Auth\\Auth_Acl_Driver'			=> __DIR__.'/classes/auth/acl/driver.php',
	'Auth\\Auth_Acl_Simpleacl'		=> __DIR__.'/classes/auth/acl/simpleacl.php',
	'Auth\\Auth_Group_Driver'		=> __DIR__.'/classes/auth/group/driver.php',
	'Auth\\Auth_Group_Simplegroup'	=> __DIR__.'/classes/auth/group/simplegroup.php',
	'Auth\\Auth_Login_Driver'		=> __DIR__.'/classes/auth/login/driver.php',
	'Auth\\Auth_Login_Simpleauth'	=> __DIR__.'/classes/auth/login/simpleauth.php',
));


/* End of file bootstrap.php */
