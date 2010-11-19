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

class Fuel_DB_Query {

	public $type = DB::SELECT;

	protected $query = '';

	/**
	 * Sets up the query to get ready to start processing.
	 * 
	 * @access	public
	 * @param	int		query type
	 * @param	string	the sql query
	 * @return	void
	 */
	public function __construct($type, $query)
	{
		$this->type = $type;
		$this->query = trim($query);
	}

	public function execute($db = NULL)
	{
		$db = DB::instance($db);
		
		// If no type was given then it must have been a full query
		if ($this->type === NULL)
		{
			switch (substr($this->query, 0, 6))
			{
				case 'SELECT':
					$this->type = DB::SELECT;
					break;
				case 'INSERT':
					$this->type = DB::INSERT;
					break;
				case 'UPDATE':
					$this->type = DB::UPDATE;
					break;
				case 'DELETE':
					$this->type = DB::DELETE;
					break;
			}
		}
		return $db->query($this->type, $this->query);
	}
}


/* End of file query.php */