<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * Driver for sending using the sendmail executable.
 * 
 * Based on the CodeIgniter Email class.
 *
 * @package		Fuel
 * @version		1.0
 * @author		DudeAmI aka Kris <dudeami0@gmail.com>
 * @license		MIT License
 * @copyright	2010 - 2011 Fuel Development Team
 * @link		http://fuelphp.com
 * @link		http://codeigniter.com/
 */

namespace Fuel\Core;

class Email_Sendmail extends Email_Driver {

	public function __construct($config) {
		parent::__construct($config);
	}

	/**
	 * Sends the email using the sendmail protocol
	 * 
	 * @return boolean	True if successful, false if not.
	 */
	protected function _send() {

		$return = true;
		// Check if the file exists
		if (file_exists($this->sendmail_path)) {
			// Start sendmail
			$fp = @popen($this->sendmail_path . " -oi -t -d", 'w');

			// Check if sendmail is not
			if ($fp === FALSE OR $fp === NULL) {
				// server probably has popen disabled, so nothing we can do to get a verbose error.
				$return = false;
				$this->_debug_message('Could not use popen, are you sure the function is enabled?', 'error');
			} else {
				// Combine the headers and message to be sent...
				fputs($fp, $this->_headers . $this->_message);

				// Get the status
				$status = pclose($fp);

				// Send an error if the email failed
				if ($status != 0) {
					$return = false;
					$this->_debug_message('Could not send email with sendmail. Exited with status code ' . $status . '.', 'error');
				}
			}
		} else {
			$return = false;
			$this->_debug_message('Could not find the sendmail executable. Make sure sendmail_path is the executables location', 'error');
		}
		// True if no errors, false if errors
		return $return;

	}

}