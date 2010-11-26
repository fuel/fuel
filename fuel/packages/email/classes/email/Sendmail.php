<?php

namespace Email;

/**
 * Driver for sending with sendmail
 * Based heavily on the code from the Email class in Codeigniter.
 */
class Sendmail {

	protected $email = null;

	public function __construct($that) {
		$this->email =& $that;
	}

	/**
	 * Sends the email using the sendmail protocol
	 * @return boolean True if successful, false if not.
	 */
	public function send() {

		$return = true;
		// Check if the file exists
		if (file_exists($this->email->sendmail_path)) {
			// Start sendmail
			$fp = @popen($this->email->sendmail_path . " -oi -t -d", 'w');

			// Check if sendmail is not
			if ($fp === FALSE OR $fp === NULL) {
				// server probably has popen disabled, so nothing we can do to get a verbose error.
				$return = false;
				$this->email->_debug_message('Could not use popen, are you sure the function is enabled?', 'error');
			} else {
				// Combine the headers and message to be sent...
				fputs($fp, $this->email->_headers . $this->email->_message);

				// Get the status
				$status = pclose($fp);

				// Send an error if the email failed
				if ($status != 0) {
					$return = false;
					$this->email->_debug_message('Could not send email with sendmail. Exited with status code ' . $status . '.', 'error');
				}
			}
		} else {
			$return = false;
			$this->email->_debug_message('Could not find the sendmail executable. Make sure sendmail_path is the executables location', 'error');
		}
		// True if no errors, false if errors
		return $return;

	}

}
?>
