<?php
return array(
	'_root_'  => 'welcome/index',  // The default route
	'_404_'   => 'welcome/404',    // The main 404 route
    '_500_'   => 'welcome/500',    // The main 500 route
	
	'hello(/:name)?' => array('welcome/hello', 'name' => 'hello'),
);