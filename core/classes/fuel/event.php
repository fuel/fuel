<?php defined('COREPATH') or die('No direct script access.');
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Dan Horrigan
 * @author		Eric Barnes
 * @author		Harro "WanWizard" Verton
 * @license		MIT License
 * @copyright	2010 Dan Horrigan
 * @link		http://fuelphp.com
 */

class Fuel_Event {

	/**
	 * @var	array	An array of listeners
	 */
	protected static $_events = array();

	// --------------------------------------------------------------------

	/**
	 * Class init, setup the shutdown event
	 *
	 * @access	public
	 * @param	void
	 * @return	void
	 */
	public function _init()
	{
		static $_init_done = false;

		// make sure we're called on shutdown
		if ( ! $_init_done)
		{
			register_shutdown_function('Fuel_Event::shutdown');
			$_init_done = true;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Register
	 *
	 * Registers a Callback for a given event
	 *
	 * @access	public
	 * @param	string	The name of the event
	 * @param	mixed	callback information
	 * @return	void
	 */
	public static function register()
	{
		// get any arguments passed
		$callback = func_get_args();

		// if the arguments are valid, register the event
		if (isset($callback[0]) && is_string($callback[0]) && isset($callback[1]) && is_callable($callback[1]))
		{
			// make sure we have an array for this event
			isset(self::$_events[$callback[0]]) OR self::$_events[$callback[0]] = array();

			// store the callback on the call stack
			array_unshift(self::$_events[$callback[0]], $callback);

			// and report success
			return true;
		}
		else
		{
			// can't register the event
			return false;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Trigger
	 *
	 * Triggers an event and returns the results.  The results can be returned
	 * in the following formats:
	 *
	 * 'array'
	 * 'json'
	 * 'serialized'
	 * 'string'
	 *
	 * @access	public
	 * @param	string	The name of the event
	 * @param	mixed	Any data that is to be passed to the listener
	 * @param	string	The return type
	 * @return	mixed	The return of the listeners, in the return type
	 */
	public static function trigger($event, $data = '', $return_type = 'string')
	{
		$calls = array();

		// check if we have events registered
		if (self::has_events($event))
		{
			// process them
			foreach (self::$_events[$event] as $arguments)
			{
				// get rid of the event name
				array_shift($arguments);

				// get the callback method
				$callback = array_shift($arguments);

				// call the callback event
				if (is_callable($callback))
				{
					$calls[] = call_user_func($callback, $data, $arguments);
				}
			}
		}

		return self::_format_return($calls, $return_type);
	}

	// --------------------------------------------------------------------

	/**
	 * method called by register_shutdown_event
	 *
	 * @access	public
	 * @param	void
	 * @return	void
	 */
	public function shutdown()
	{
		// shutdown events have to be executed in reverse order
		self::$_events['shutdown'] = array_reverse(self::$_events['shutdown']);

		// trigger the shutdown events
		self::trigger('shutdown');
	}

	// --------------------------------------------------------------------

	/**
	 * Has Listeners
	 *
	 * Checks if the event has listeners
	 *
	 * @access	public
	 * @param	string	The name of the event
	 * @return	bool	Whether the event has listeners
	 */
	public static function has_events($event)
	{
		if (isset(self::$_events[$event]) AND count(self::$_events[$event]) > 0)
		{
			return TRUE;
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Format Return
	 *
	 * Formats the return in the given type
	 *
	 * @access	protected
	 * @param	array	The array of returns
	 * @param	string	The return type
	 * @return	mixed	The formatted return
	 */
	protected static function _format_return(array $calls, $return_type)
	{
		switch ($return_type)
		{
			case 'array':
				return $calls;
				break;
			case 'json':
				return json_encode($calls);
				break;
			case 'none':
				return;
			case 'serialized':
				return serialize($calls);
				break;
			case 'string':
				$str = '';
				foreach ($calls as $call)
				{
					$str .= $call;
				}
				return $str;
				break;
			default:
				return $calls;
				break;
		}

		return FALSE;
	}
}

/* End of file event.php */
