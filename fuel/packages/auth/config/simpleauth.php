<?php

return array(
	'groups' => array(
		-1	=> array('name' => 'Banned', 'roles' => array('banned')),
		0	=> array('name' => 'Guests', 'roles' => array()),
		1	=> array('name' => 'Users', 'roles' => array('user')),
		50	=> array('name' => 'Moderators', 'roles' => array('user', 'moderator')),
		100	=> array('name' => 'Administrators', 'roles' => array('user', 'moderator', 'admin')),
	),

	'roles' => array(
		'#'          => array('website' => array('read')), // default rights
		'banned'     => false,
		'user'       => array('comments' => array('create', 'read')),
		'moderator'  => array('comments' => array('update', 'delete')),
		'admin'      => array(
			'website'  => array('create', 'update', 'delete'),
			'admin'    => array('create', 'read', 'update', 'delete'),
		),
		'super'      => true,
	),

    'table_name' => 'simpleusers',
);