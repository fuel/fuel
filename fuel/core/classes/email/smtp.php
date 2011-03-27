<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * Driver for sending email using the SMTP protocol.
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

class Email_Smtp extends Email_Driver {

	protected $_smtp_connect;

	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Send using the SMTP protocol.
	 * @return boolean True if successful, false if not.
	 */
	protected function _send()
	{
		$return = false;
		if ($this->smtp_vars['host'] == '')
		{
			$this->_debug_message('SMTP Host is not set.', 'error');
			$return = false;
		}
		else
		{
			$this->_smtp_connect();
			$this->_smtp_authenticate();

			if ($this->_send_command('from', $this->sender))
			{
				$continue = true;
				$to = $this->recipients;
				if ( ! $this->_bcc_batch_running)
				{
					foreach ($to as $val)
					{
						$continue = $continue && $this->_send_command('to', $val);
					}
				}
				if ($continue)
				{
					$cc = $this->_sanitize_emails($this->cc_recipients);
					if (count($cc) > 0)
					{
						foreach ($cc as $val)
						{
							if ($val != "")
							{
								$this->_send_command('to', $val);
							}
						}
					}
					if ($continue)
					{
						$bcc = $this->_sanitize_emails($this->bcc_recipients);
						if (count($bcc) > 0)
						{
							foreach ($bcc as $val)
							{
								if ($val != "")
								{
									$continue = $continue && $this->_send_command('to', $val);
								}
							}
						}
						if ($this->_send_command('data'))
						{
							if ($this->_send_data($this->_headers.preg_replace('/^\./m', '..$1', $this->_message).'.'))
							{

								$reply = $this->_get_smtp_data();

								if (strncmp($reply, '250', 3) != 0)
								{
									$this->_debug_message('SMTP Errored out, replied '.$reply, 'error');
								}
								else
								{
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
	private function _smtp_connect()
	{
		$return = false;
		$this->_smtp_connect = fsockopen(
			$this->smtp_vars['host'],
			$this->smtp_vars['port'],
			$errno,
			$errstr,
			$this->smtp_vars['timeout']
		);

		if ( ! is_resource($this->_smtp_connect))
		{
			$this->_debug_message('Could not send using SMTP. (#'.$errno.') '.$errstr);
		}
		else
		{
			$this->_debug_message('SMTP Sent: '.$this->_get_smtp_data());
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
	private function _send_command($cmd, $data = '')
	{
		switch ($cmd)
		{
			case 'hello' :

				if ($this->smtp_vars['auth'] || $this->encoding == '8bit')
					$this->_send_data('EHLO '.$this->_get_hostname());
				else
					$this->_send_data('HELO '.$this->_get_hostname());

				$resp = 250;
				break;
			case 'from' :

				$this->_send_data('MAIL FROM:'.$data);

				$resp = 250;
				break;
			case 'to' :

				$this->_send_data('RCPT TO:<'.$data.'>');

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

		$this->_debug_message($cmd.": ".($cmd == 'data' ? 'Headers and Message Body (printed below)' : $reply), 'info');

		if (substr($reply, 0, 3) != $resp)
		{
			$this->_debug_message('Error code returned was '.$reply.', expecting '.$resp, 'error');
			return FALSE;
		}

		if ($cmd == 'quit')
		{
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
	private function _smtp_authenticate()
	{
		$return = true;
		if ($this->smtp_vars['auth'])
		{
			if ($this->smtp_vars['user'] == "" AND $this->smtp_vars['pass'] == "")
			{
				$this->_debug_message('Failed to authenticate with SMTP, no username and password set.', 'error');
				$return = false;
			}
			else
			{
				$this->_send_data('AUTH LOGIN');
				$reply = $this->_get_smtp_data();
				if (strncmp($reply, '334', 3) != 0)
				{
					$this->_debug_message('Failed to authenticate with SMTP, invalid user/pass.', 'error');
					$return = false;
				}
				else
				{
					$this->_send_data(base64_encode($this->smtp_vars['user']));

					$reply = $this->_get_smtp_data();

					if (strncmp($reply, '334', 3) != 0)
					{
						$this->_debug_message('Failed to authenticate with SMTP, incorrect username.', 'error');
						;
						$return = false;
					}
					else
					{

						$this->_send_data(base64_encode($this->smtp_vars['pass']));

						$reply = $this->_get_smtp_data();

						if (strncmp($reply, '235', 3) != 0)
						{
							$this->_debug_message('Failed to authenticate with SMTP, incorrect password.', 'error');
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
	private function _send_data($data)
	{
		if ( ! fwrite($this->_smtp_connect, $data.$this->newline))
		{
			$this->_debug_message('Failed to send SMTP data.', 'error');
			return FALSE;
		}
		else
		{
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
	private function _get_smtp_data()
	{
		$data = "";

		while ($str = fgets($this->_smtp_connect, 512))
		{
			$data .= $str;

			if (substr($str, 3, 1) == " ")
			{
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
	private function _get_hostname()
	{
		return (isset($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : 'localhost.localdomain';
	}

}
