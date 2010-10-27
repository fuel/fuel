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

class Fuel_DB_MySQL_Driver extends DB_Driver {
	
	public function connect()
	{
		if ($this->_conn !== NULL)
		{
			return;
		}

		extract($this->_config['connection']);
		
		try
		{
			if ($persistent)
			{
				$this->_conn = mysql_pconnect($hostname, $username, $password);
			}
			else
			{
				$this->_conn = mysql_connect($hostname, $username, $password, TRUE);
			}
		}
		catch (Fuel_Exception $e)
		{
			$this->_conn = NULL;
			throw new Fuel_Exception(mysql_error(), mysql_errno());
		}
		
		$this->_select_db($database);
	}

	private function _select_db($database)
	{
		if ( ! mysql_select_db($database, $this->_conn))
		{
			throw new Database_Exception(mysql_error($this->_conn), mysql_errno($this->_conn));
		}
	}

	public function disconnect()
	{
		$result = TRUE;

		try
		{
			if (is_resource($this->_conn))
			{
				if ($result = mysql_close($this->_conn))
				{
					$this->_conn = NULL;
				}
			}
		}
		catch (Exception $e)
		{
			$result = ! is_resource($this->_conn);
		}

		return $result;
	}

	public function query($type, $sql, $as_object = TRUE)
	{
		if (($result = mysql_query($sql, $this->_conn)) === FALSE)
		{
			throw new Fuel_Exception(mysql_error($this->_conn), mysql_errno($this->_conn));
		}

		// Set the last query
		$this->last_query = $sql;

		if ($type === DB::SELECT)
		{
			return new DB_MySQL_Result($result, $sql, $as_object);
		}
		elseif ($type === Database::INSERT)
		{
			return array(
				mysql_insert_id($this->_conn),
				mysql_affected_rows($this->_conn),
			);
		}
		else
		{
			return mysql_affected_rows($this->_conn);
		}
	}
	
}

/* End of file driver.php */