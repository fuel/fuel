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
		'#' => array('website' => 'r'), // default rights
		'banned' => false,
		'user' => array('comments' => 'cr'),
		'moderator' => array('comments' => 'ud'),
		'admin' => array('website' => 'cud', 'admin' => 'crud'),
		'super' => true,
	),
);