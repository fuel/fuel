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



return array(

	/*
	| What format should the data be returned in by default?
	|
	|	Default: xml
	|
	*/
	'default_format' => 'xml',

	/*
	| Name for the password protected REST API displayed on login dialogs
	|
	|	E.g: My Secret REST API
	|
	*/
	'realm' => 'REST API',

	/*
	| Is login required and if so, which type of login?
	|
	|	'' = no login required, 'basic' = unsecure login, 'digest' = more secure login
	|
	*/
	'auth' => '',

	/*
	| Array of usernames and passwords for login
	|
	|	array('admin' => '1234')
	|
	*/
	'valid_logins' => array('admin' => '1234'),

);

/* End of file config/asset.php */