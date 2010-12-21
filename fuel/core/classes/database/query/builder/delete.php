<?php
/**
 * Database query builder for DELETE statements.
 *
 * @package    Kohana/Database
 * @category   Query
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license
 */

namespace Fuel\Core;

class Database_Query_Builder_Delete extends Database_Query_Builder_Where {

	// DELETE FROM ...
	protected $_table;

	/**
	 * Set the table for a delete.
	 *
	 * @param   mixed  table name or array($table, $alias) or object
	 * @return  void
	 */
	public function __construct($table = NULL)
	{
		if ($table)
		{
			// Set the inital table name
			$this->_table = $table;
		}

		// Start the query with no SQL
		return parent::__construct('', Database::DELETE);
	}

	/**
	 * Sets the table to delete from.
	 *
	 * @param   mixed  table name or array($table, $alias) or object
	 * @return  $this
	 */
	public function table($table)
	{
		$this->_table = $table;

		return $this;
	}

	/**
	 * Compile the SQL query and return it.
	 *
	 * @param   object  Database instance
	 * @return  string
	 */
	public function compile(Database $db)
	{
		// Start a deletion query
		$query = 'DELETE FROM '.$db->quote_table($this->_table);

		if ( ! empty($this->_where))
		{
			// Add deletion conditions
			$query .= ' WHERE '.$this->_compile_conditions($db, $this->_where);
		}

		if ( ! empty($this->_order_by))
		{
			// Add sorting
			$query .= ' '.$this->_compile_order_by($db, $this->_order_by);
		}

		if ($this->_limit !== NULL && substr($db->_db_type, 0, 6) !== 'sqlite')
		{
			// Add limiting
			$query .= ' LIMIT '.$this->_limit;
		}

		return $query;
	}

	public function reset()
	{
		$this->_table = NULL;
		$this->_where = array();

		$this->_parameters = array();

		return $this;
	}

} // End Database_Query_Builder_Delete
