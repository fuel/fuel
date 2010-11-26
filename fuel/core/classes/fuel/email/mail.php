<?php

namespace Fuel;

/**
 * Driver for sending with mail
 * Based heavily on the code from the Email class in Codeigniter.
 */
class Email_Mail {

	protected $email = null;

	public function __construct($that) {
		$this->email =& $that;
	}

	/**
	 * Used to send an email using mail function of php.
	 * @return boolean True if successful, false if not.
	 */
	public function send() {
		$return = false;
		$to = !$this->email->_bcc_batch_running ? implode(', ', $this->email->_sanitize_emails($this->email->recipients)) : '';
		$subject = $this->email->_prep_q_encoding($this->email->subject);
		$message = $this->email->_message;
		$headers = $this->email->_headers;
		$sender = $this->email->_clean_email($this->email->sender);
		if ($this->email->safe_mode == TRUE && mail($to, $subject, $message, $headers)) {
			$return = true;
		} else if (mail($to, $subject, $message, $headers, "-f " . $sender)) {
			// most documentation of sendmail using the "-f" flag lacks a space after it, however
			// we've encountered servers that seem to require it to be in place.
			$return = true;
		}
		return $return;
	}

}

?>
