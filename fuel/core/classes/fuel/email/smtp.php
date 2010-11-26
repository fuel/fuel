<?php

namespace Fuel;

/**
 * Driver for sending with smtp
 * Based heavily on the code from the Email class in Codeigniter.
 */
class Email_Smtp {

	protected $email = null;

	private $_smtp_connect;

	public function __construct($that) {
		$this->email =& $that;
	}

	/**
	 * Send using SMTP
	 *
	 * @access	private
	 * @return boolean True if successful, false if not.
	 */
	public function send() {
		$return = false;
		if ($this->email->smtp_vars['host'] == '') {
			$this->email->_debug_message('SMTP Host is not set.', 'error');
			$return = false;
		} else {
			$this->_smtp_connect();
			$this->_smtp_authenticate();

			if ($this->_send_command('from', $this->email->sender)) {
				$continue = true;
				$to = $this->email->recipients;
				if (!$this->email->_bcc_batch_running) {
					foreach ($to as $val) {
						$continue = $continue && $this->_send_command('to', $val);
					}
				}
				if ($continue) {
					$cc = $this->email->_sanitize_emails($this->email->cc_recipients);
					if (count($cc) > 0) {
						foreach ($cc as $val) {
							if ($val != "") {
								$this->_send_command('to', $val);
							}
						}
					}
					if ($continue) {
						$bcc = $this->email->_sanitize_emails($this->email->bcc_recipients);
						if (count($bcc) > 0) {
							foreach ($bcc as $val) {
								if ($val != "") {
									$continue = $continue && $this->_send_command('to', $val);
								}
							}
						}
						if ($this->_send_command('data')) {
							if ($this->_send_data($this->email->_headers . preg_replace('/^\./m', '..$1', $this->email->_message) . '.')) {

								$reply = $this->_get_smtp_data();

								if (strncmp($reply, '250', 3) != 0) {
									$this->email->_debug_message('SMTP Errored out, replied ' . $reply, 'error');
								} else {
									$return = true;
									$this->_send_command('quit');
								}
							}
						}
					}
				}
			}
		}
		return $return;
	}

	// --------------------------------------------------------------------

	/**
	 * SMTP Connect
	 *
	 * @access	private
	 * @param	string
	 * @return	string
	 */
	private function _smtp_connect() {
		$return = false;
		$this->_smtp_connect = fsockopen($this->email->smtp_vars['host'],
						25,
						$errno,
						$errstr,
						$this->email->smtp_vars['timeout']);

		if (!is_resource($this->_smtp_connect)) {
			$this->email->_debug_message('Could not send using SMTP. (#' . $errno . ') ' . $errstr);
		} else {
			$this->email->_debug_message('SMTP Sent: ' . $this->_get_smtp_data());
			return $this->_send_command('hello');
		}
		return $return;
	}

	// --------------------------------------------------------------------

	/**
	 * Send SMTP command
	 *
	 * @access	private
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	private function _send_command($cmd, $data = '') {
		switch ($cmd) {
			case 'hello' :

				if ($this->email->smtp_vars['auth'] || $this->email->encoding == '8bit')
					$this->_send_data('EHLO ' . $this->_get_hostname());
				else
					$this->_send_data('HELO ' . $this->_get_hostname());

				$resp = 250;
				break;
			case 'from' :

				$this->_send_data('MAIL FROM:<' . $data . '>');

				$resp = 250;
				break;
			case 'to' :

				$this->_send_data('RCPT TO:<' . $data . '>');

				$resp = 250;
				break;
			case 'data' :

				$this->_send_data('DATA');

				$resp = 354;
				break;
			case 'quit' :

				$this->_send_data('QUIT');

				$resp = 221;
				break;
		}

		$reply = $this->_get_smtp_data();

		$this->email->_debug_message($cmd . ": " . ($cmd == 'data' ? 'Headers and Message Body (printed below)' : $reply), 'info');

		if (substr($reply, 0, 3) != $resp) {
			$this->email->_debug_message('Error code returned was ' . $reply . ', expecting ' . $resp, 'error');
			return FALSE;
		}

		if ($cmd == 'quit') {
			fclose($this->_smtp_connect);
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 *  SMTP Authenticate
	 *
	 * @access	private
	 * @return	bool
	 */
	private function _smtp_authenticate() {
		$return = true;
		if ($this->email->smtp_vars['auth']) {
			if ($this->email->smtp_vars['user'] == "" AND $this->email->smtp_vars['pass'] == "") {
				$this->_debug_message('Failed to authenticate with SMTP, no username and password set.', 'error');
				$return = false;
			} else {
				$this->_send_data('AUTH LOGIN');
				$reply = $this->_get_smtp_data();
				if (strncmp($reply, '334', 3) != 0) {
					$this->email->_debug_message('Failed to authenticate with SMTP, invalid user/pass.', 'error');
					$return = false;
				} else {
					$this->_send_data(base64_encode($this->email->smtp_vars['user']));

					$reply = $this->_get_smtp_data();

					if (strncmp($reply, '334', 3) != 0) {
						$this->email->_debug_message('Failed to authenticate with SMTP, incorrect username.', 'error');;
						$return = false;
					} else {

						$this->_send_data(base64_encode($this->email->smtp_vars['pass']));

						$reply = $this->_get_smtp_data();

						if (strncmp($reply, '235', 3) != 0) {
							$this->email->_debug_message('Failed to authenticate with SMTP, incorrect password.', 'error');
							$return = false;
						}
					}
				}
			}
		}
		return $return;
	}

	// --------------------------------------------------------------------

	/**
	 * Send SMTP data
	 *
	 * @access	private
	 * @return	bool
	 */
	private function _send_data($data) {
		if (!fwrite($this->_smtp_connect, $data . $this->email->newline)) {
			$this->email->_debug_message('Failed to send SMTP data.', 'error');
			return FALSE;
		} else {
			return TRUE;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Get SMTP data
	 *
	 * @access	private
	 * @return	string
	 */
	private function _get_smtp_data() {
		$data = "";

		while ($str = fgets($this->_smtp_connect, 512)) {
			$data .= $str;

			if (substr($str, 3, 1) == " ") {
				break;
			}
		}

		return $data;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Hostname
	 *
	 * @access	private
	 * @return	string
	 */
	private function _get_hostname() {
		return (isset($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : 'localhost.localdomain';
	}

}