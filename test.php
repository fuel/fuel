<?php

include 'fuel/core/classes/autoloader.php';

Fuel\Core\Autoloader::register();
Fuel\Core\Autoloader::add_classes(array(
	'Fuel\\Core\\Arr'	=> __DIR__.'/fuel/core/classes/arr.php',
));

$ar = array('test' => 'foo', 'bar' => 'baz');

var_dump(Arr::element($ar, 'bar'));