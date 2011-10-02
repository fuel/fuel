<?php
/**
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
	'active' => Fuel::$env,

	Fuel::DEVELOPMENT => array(
		'type'			=> 'pdo',
		'connection'	=> array(
			'dsn'        => 'mysql:host=localhost;dbname=fuel_dev',
			'username'   => 'root',
			'password'   => '',
			'persistent' => false,
		),
		'identifier' => '`',
		'table_prefix' => '',
		'charset'      => 'utf8',
		'caching'      => false,
		'profiling'    => false,
	),

	Fuel::PRODUCTION => array(
		'type'			=> 'pdo',
		'connection'	=> array(
			'dsn'        => 'mysql:host=localhost;dbname=fuel_prod',
			'username'   => 'root',
			'password'   => '',
			'persistent' => false,
		),
		'identifier' => '`',
		'table_prefix' => '',
		'charset'      => 'utf8',
		'caching'      => false,
		'profiling'    => false,
	),

	Fuel::TEST => array(
		'type'			=> 'pdo',
		'connection'	=> array(
			'dsn'        => 'mysql:host=localhost;dbname=fuel_test',
			'username'   => 'root',
			'password'   => '',
			'persistent' => false,
		),
		'identifier' => '`',
		'table_prefix' => '',
		'charset'      => 'utf8',
		'caching'      => false,
		'profiling'    => false,
	),

	Fuel::STAGE => array(
		'type'			=> 'pdo',
		'connection'	=> array(
			'dsn'        => 'mysql:host=localhost;dbname=fuel_stage',
			'username'   => 'root',
			'password'   => '',
			'persistent' => false,
		),
		'identifier' => '`',
		'table_prefix' => '',
		'charset'      => 'utf8',
		'caching'      => false,
		'profiling'    => false,
	),

	'redis' => array(
		'default' => array(
			'hostname'	=> '127.0.0.1',
			'port'		=> 6379,
		)
	),

);

/* End of file db.php */
