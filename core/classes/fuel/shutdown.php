<?php defined('COREPATH') or die('No direct script access.');
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Harro "WanWizard" Verton
 * @license		MIT License
 * @copyright	2010 Dan Horrigan
 * @link		http://fuelphp.com
 */

class Fuel_Shutdown {

	/*
	 * @var	storage for the shutdown callback methods
	 */
	private static $callbacks;

	/**
	 * Register a new shutdown event
	 *
	 * @access	public
	 * @return	void
	 */
	public function event()
	{
		// make sure we're initialized
		if ( ! is_array(self::$callbacks))
		{
			// initialize the callback array
			self::$callbacks = array();

			// make sure we're called on shutdown
			register_shutdown_function('Shutdown::execute');
		}

		// get any arguments passed
		$callback = func_get_args();

		// make sure there are any
		if (empty($callback))
		{
			throw new Fuel_Exception('No callback passed to Shutdown::event()');
		}

		// we've got parameters. is the first callable?
		if (!is_callable($callback[0]))
		{
			throw new Fuel_Exception('Invalid callback passed to Shutdown::event()');
		}

		// store the callback
		self::$callbacks[] = $callback;
	}

	/**
	 * Execute all registered shutdown methods
	 *
	 * @access	public
	 * @return	void
	 */
	public static function execute()
	{
		// make sure we're initialized
		if ( is_array(self::$callbacks))
		{
			// loop through the registered shutdown events
			foreach (self::$callbacks as $arguments)
			{
				// get the callback method
				$callback = array_shift($arguments);

				// and call it. use the remaining argements as method arguments
				call_user_func_array($callback, $arguments);
			}
		}
	}
}

/* End of file shutdown.php */
