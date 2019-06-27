<?php
/**
 * Fuel is a fast, lightweight, community driven PHP 5.4+ framework.
 *
 * @package    Fuel
 * @version    1.9-dev
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2019 Fuel Development Team
 * @link       https://fuelphp.com
 */

/**
 * -----------------------------------------------------------------------------
 *  Database settings for testing environment
 * -----------------------------------------------------------------------------
 *
 *  This environment is primarily used by unit tests, to run on a
 *  controlled environment.
 *
 *  These settings get merged with the global settings.
 *
 */

return array(
	'default' => array(
		'connection' => array(
			'dsn'      => 'mysql:host=localhost;dbname=fuel_test',
			'username' => 'fuel_app',
			'password' => 'super_secret_password',
		),
	),
);
