<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * Driver for sending using the PHP mail() function.
 *
 * Based on the CodeIgniter Email class.
 *
 * @package		Fuel
 * @version		1.0
 * @author		DudeAmI aka Kris <dudeami0@gmail.com>
 * @license		MIT License
 * @copyright	2010 Dan Horrigan
 * @link		http://fuelphp.com
 * @link		http://codeigniter.com/
 */

namespace Fuel\Core;

class Email_Mail extends Email_Driver {

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	/**
	 * Used to send an email using mail function of php.
	 * 
	 * @return boolean	True if successful, false if not.
	 */
	protected function _send() {
		$return = false;
		$to = !$this->_bcc_batch_running ? implode(', ', $this->_sanitize_emails($this->recipients)) : '';
		$subject = $this->_prep_q_encoding($this->subject);
		$message = $this->_message;
		$headers = $this->_headers;
		$sender = $this->_clean_email($this->sender);
		if ($this->safe_mode == TRUE && mail($to, $subject, $message, $headers)) {
			$return = true;
		} else if (mail($to, $subject, $message, $headers, "-f " . $sender)) {
			// most documentation of sendmail using the "-f" flag lacks a space after it, however
			// we've encountered servers that seem to require it to be in place.
			$return = true;
		}
		return $return;
	}

}