<?php defined('COREPATH') or die('No direct script access.');
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Fuel Development Team
 * @license		MIT License
 * @copyright	2010 Dan Horrigan
 * @link		http://fuelphp.com
 */

class Fuel_Active_Record {

	/**
	 * @var	array	The database connection
	 */
	protected $db_conn = NULL;

	/**
	 * @var	array	The database name
	 */
	protected $database = NULL;

	/**
	 * @var	array	The table name
	 */
	protected $table = NULL;

	/**
	 * @var	array	The table's primary key
	 */
	protected $primary_key = 'id';

	/**
	 * @var	array	The table columns
	 */
	protected $columns = array();

	/**
	 * @var	array	The has many association
	 */
	protected $has_many = array();

	/**
	 * @var	array	The has one association
	 */
	protected $has_one = array();

	/**
	 * @var	array	The belongs to association
	 */
	protected $belongs_to = array();

	/**
	 * @var	array	The many to many association
	 */
	protected $many_to_many = array();

	/**
	 * @var	array	Holds all of the associations
	 */
	protected $associations = array();


	public function __construct()
	{
		foreach (array('has_many', 'has_one', 'belongs_to', 'many_to_many') as $type)
		{
			if ( ! is_array($this->{$type}))
			{
				$this->{$type} = array($this->{$type});
			}
			foreach ($this->{$type} as $association)
			{
				$this->associations[$type] = $association;
			}
		}
	}



	protected function _pre_find($query) { }
	protected function _post_find($result) { }

	protected function _pre_save($query) { }
	protected function _post_save($result) { }

	protected function _pre_update($query) { }
	protected function _post_update($result) { }

	protected function _pre_delete($query) { }
	protected function _post_delete($result) { }

	protected function _pre_validate($data) { }
	protected function _post_validate($result) { }

}

/* End of file fuel_active_record.php */