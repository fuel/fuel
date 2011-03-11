<?php
/**
 * Interact with Database Transactions
 *
 * @package    Fuel/Database
 * @category   Database
 * @author     five07
 * @copyright  (c) 2011 five07
 * @license    https://github.com/five07/Fuel-Addons/blob/master/LICENSE
 */

namespace Fuel\Core;



class Database_Transaction 
{
	protected static $_instance = null;
	protected $_db;

	public static function instance()
	{
		if (static::$_instance == null)
		{
			static::$_instance = static::factory();
		}
		return static::$_instance;
	}
	
	/**
	 * Creates a new instance
	 *
	 * @param	array	$config
	 */
	public static function factory()
	{		
		return new static();
	}
	
	/**
	*	The constructor, duh
	*/
	public function __construct()
	{
		$this->_db = Database_Connection::instance();
	}

	/**
	*	Start your transaction before a set of dependent queries
	*/
	public function start()
	{		
		$this->_db->start_transaction();
	}

	/**
	*	Complete your transaction on the set of queries
	*/
	public function complete()
	{
		if ($this->_db->trans_errors === FALSE)
		{
			static::commit();
		}
		else
		{
			static::rollback();
		}
	}
	
	/**
	*	If the group of queries had no errors, this returns TRUE
	*	Otherwise, will return FALSE
	*	
	*	@return boolean
	*/
	public function status()
	{
		return ($this->_db->trans_errors === FALSE);
	}
	
	/**
	*	Commit the successful queries and reset AUTOCOMMIT
	*	This is called automatically if you use Database_Transaction::complete()
	*	It can also be used manually for testing
	*/
	public function commit()
	{
		$this->_db->commit_transaction();
	}
	
	/**
	*	Rollback the failed queries and reset AUTOCOMMIT
	*	This is called automatically if you use Database_Transaction::complete()
	*	It can also be used manually for testing
	*/
	public function rollback()
	{
		$this->_db->rollback_transaction();
	}
	
	/**
	*	Return the database errors
	*	
	*	@return mixed (array or false)
	*/
	public function errors()
	{
		return $this->_db->trans_errors;
	}
	

} // End Database_Transaction
