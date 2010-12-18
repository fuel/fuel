<?php

namespace Fuel\Auth\Model;
use ActiveRecord;

/*
	CREATE TABLE `simpleusers` (
		`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`username` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
		`password` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
		`group` INT NOT NULL DEFAULT 1 ,
		`email` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
		`last_login` VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
		`login_hash` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
		`profile_fields` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
		UNIQUE (
			`username` ,
			`email`
		)
	)
 */

class SimpleUser extends ActiveRecord\Model {}

/* End of file simpleuser.php */