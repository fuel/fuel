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

// --------------------------------------------------------------------

/**
 * Session Class
 *
 * @package		Fuel
 * @category	Sessions
 * @author		Harro "WanWizard" Verton
 */

abstract class Fuel_Session_Driver {

	protected $valid_storage = array('cookie');

	/**
	 * Create a new session.
	 *
	 * @abstract
	 * @access	public
	 * @return	void
	 */
	abstract public function create();

	/**
	 * Read the current session, create one is none exists.
	 *
	 * @abstract
	 * @access	public
	 * @return	void
	 */
	abstract public function read();

	/**
	 * Write current session.
	 *
	 * @abstract
	 * @access	public
	 * @return	void
	 */
	abstract public function write();

	/**
	 * Destroy the current session.
	 *
	 * @abstract
	 * @access	public
	 * @return	void
	 */
	abstract public function destroy();

	/**
	 * Get a session variable
	 *
	 * @abstract
	 * @access	public
	 * @return	void
	 */
	abstract public function get($name);

	/**
	 * set a session variable.
	 *
	 * @abstract
	 * @access	public
	 * @return	void
	 */
	abstract public function set($name, $value);

	/**
	 * delete a session variable.
	 *
	 * @abstract
	 * @access	public
	 * @return	void
	 */
	abstract public function delete($name);

	/**
	 * Get a flash session variable
	 *
	 * @abstract
	 * @access	public
	 * @return	void
	 */
	abstract public function get_flash($name);

	/**
	 * set a flash session variable.
	 *
	 * @abstract
	 * @access	public
	 * @return	void
	 */
	abstract public function set_flash($name, $value);

	/**
	 * delete a flash session variable.
	 *
	 * @abstract
	 * @access	public
	 * @return	void
	 */
	abstract public function delete_flash($name);

	/**
	 * keep a flash session variable.
	 *
	 * @abstract
	 * @access	public
	 * @return	void
	 */
	abstract public function keep_flash($name);
}

/* End of file driver.php */
