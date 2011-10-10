<?php
/**
 * Base Database Config.
 *
 * See the individual environment DB configs for specific config information.
 */

return array(
	'active' => 'default',

	'redis' => array(
		'default' => array(
			'hostname'  => '127.0.0.1',
			'port'      => 6379,
		)
	),

);
