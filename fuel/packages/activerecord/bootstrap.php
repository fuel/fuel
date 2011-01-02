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
	'ActiveRecord\\Association'	=> __DIR__.'/classes/association.php',
	'ActiveRecord\\BelongsTo'	=> __DIR__.'/classes/belongsto.php',
	'ActiveRecord\\Exception'	=> __DIR__.'/classes/exception.php',
	'ActiveRecord\\HasMany'		=> __DIR__.'/classes/hasmany.php',
	'ActiveRecord\\HasOne'		=> __DIR__.'/classes/hasone.php',
	'ActiveRecord\\Model'		=> __DIR__.'/classes/model.php',
));


/* End of file bootstrap.php */