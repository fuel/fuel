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



Autoloader::add_classes(array(
	'Orm\\Model'		=> __DIR__.'/classes/model.php',
	'Orm\\Query'		=> __DIR__.'/classes/query.php',
	'Orm\\BelongsTo'	=> __DIR__.'/classes/belongsto.php',
	'Orm\\HasMany'		=> __DIR__.'/classes/hasmany.php',
	'Orm\\HasOne'		=> __DIR__.'/classes/hasone.php',
	'Orm\\ManyMany'		=> __DIR__.'/classes/manymany.php',
	'Orm\\Relation'		=> __DIR__.'/classes/relation.php',

	// Exceptions
	'Orm\\Exception'			=> __DIR__.'/classes/exceptions.php',
	'Orm\\RecordNotFound'		=> __DIR__.'/classes/exceptions.php',
	'Orm\\UndefinedProperty'	=> __DIR__.'/classes/exceptions.php',
	'Orm\\UndefinedRelation'	=> __DIR__.'/classes/exceptions.php',
	'Orm\\InvalidObserver'		=> __DIR__.'/classes/exceptions.php',
));


/* End of file bootstrap.php */