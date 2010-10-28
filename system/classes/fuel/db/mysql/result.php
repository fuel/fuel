<?php defined('SYSPATH') or die('No direct script access.');
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

class Fuel_DB_MySQL_Result extends DB_Result {
	
	protected $_internal_row = 0;
	
	public function __construct($result, $sql)
	{
		parent::__construct($result, $sql);

		$this->_total_rows = mysql_num_rows($result);
	}
	public function __destruct()
	{
		if (is_resource($this->_result))
		{
			mysql_free_result($this->_result);
		}
	}
	
	public function seek($offest)
	{
		if ($this->offsetExists($offset) and mysql_data_seek($this->_result, $offset))
		{
			$this->_current_row = $this->_internal_row = $offset;

			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function current()
	{
		if ($this->_current_row !== $this->_internal_row and ! $this->seek($this->_current_row))
		return false;

		// Increment internal row for optimization assuming rows are fetched in order
		$this->_internal_row++;

		if ($this->_as_object)
		{
			return mysql_fetch_object($this->_result);
		}
		else
		{
			return mysql_fetch_assoc($this->_result);
		}
	}
}

/* End of file result.php */