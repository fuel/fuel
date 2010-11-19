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

abstract class Fuel_DB_Driver {
	
	/**
	 * @var	array	The db config
	 */
	protected $_config = array();

	/**
	 * @var	array	The db connection
	 */
	protected $_conn = NULL;

	/**
	 * @var	string	The last sql query to run
	 */
	public $last_query = '';

	public function __construct($name, $config)
	{
		$this->_config = $config;
	}
	
	public function __destruct()
	{
		$this->disconnect();
	}

	/**
	 * Connects to the database server.
	 *
	 * @abstract
	 * @access	public
	 * @return	void
	 */
	abstract public function connect();

	/**
	 * Disconnects from the database server.  This is automatically
	 * called on destruct.
	 *
	 * @abstract
	 * @access	public
	 * @return	void
	 */
	abstract public function disconnect();

	/**
	 * Perform an SQL query of the given type.
	 *
	 * // Make a SELECT query and use objects for results
	 * $db->query(DB::SELECT, 'SELECT * FROM groups', true);
	 *
	 * // Make a SELECT query and use "Model_User" for the results
	 * $db->query(DB::SELECT, 'SELECT * FROM users LIMIT 1', 'Model_User');
	 *
	 * @param integer DB::SELECT, DB::INSERT, etc
	 * @param string SQL query
	 * @return object Database_Result for SELECT queries
	 * @return array list (insert id, row count) for INSERT queries
	 * @return integer number of affected rows for all other queries
	 */
	abstract public function query($type, $sql, $as_object = true);
	
}

/* End of file driver.php */