<?php

/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * Base driver for Emails.
 *
 * Some code taken from the CodeIgniter Email Class, and is noted in the PHPDocs of those methods.
 *
 * @package		Fuel
 * @version		1.0
 * @author		DudeAmI aka Kris <dudeami0@gmail.com>
 * @license		MIT License
 * @copyright	2010 - 2011 Fuel Development Team
 * @link		http://fuelphp.com
 */

namespace Fuel\Core;

abstract class Email_Driver {

	// Recipient Related things

	/** @var array		An array of all recipients to add in the To: header */
	protected $recipients = array();

	/** @var array		An array of all recipients to add in the CC: header */
	protected $cc_recipients = array();

	/** @var array		An array of all recipients to add in the BCC: header */
	protected $bcc_recipients = array();

	/** @var string		The email address of the email sender. */
	protected $sender = '';

	// Content related

	/** @var string		The subject of the email. */
	protected $subject = '';

	/** @var string		The html contents of the email. */
	protected $html_contents = '';

	/** @var string		The plain text contents of the email. */
	protected $text_contents = '';

	/** @var array		An array of filesystem and dynamic attachments. */
	protected $attachments = array();

	// Other email related things
	/** @var array		An array of headers for the email. */
	protected $headers = array();

	/** @var integer	The priority of the email. 1-5 are acceptable. */
	protected $priority = 3;

	/** @var string		The encoding of the email. Currently only accepts quoted-printable. */
	protected $encoding = "quoted-printable";

	/** @var string		The charset of the email. */
	protected $charset = 'utf-8';

	/** @var string		The useragent of the email, placed in both */
	protected $useragent = 'FuelPHP';

	/** @var string		Used for supporting the original Email Class, chooses which method to use in message() and set_alt_message(). */
	protected $mailtype = 'text';

	// Options for the class

	/** @var string		New line character. \r\n according to specs, but \n for compatability. */
	protected $newline = "\n";

	/** @var string		New line character. \r\n according to specs, but \n for compatability. */
	protected $crlf = "\n";

	protected $protocol = 'mail';

	/** @var string		The location of the sendmail program. Must include the applications name at the end. */
	protected $sendmail_path = '/usr/bin/sendmail';

	/** @var boolean	If true, validates all emails. */
	protected $validity_check = false;

	/** @var array		Contains SMTP host, user, pass, port, and timeout. */
	protected $smtp_vars = array(
		'host' => '',
		'user' => '',
		'pass' => '',
		'port' => 25,
		'timeout' => 5,
		'auth' => false
	);
	/** @var string		Used to set wordwrap on or off. */
	protected $wordwrap = true;

	/** @var integer	How many characters are allowed a line with wordwrapping. */
	protected $wordwrap_width = 76;

	/** @var boolean	Enables	or disables BCC Batch Mode */
	protected $bcc_batch_mode = false;

	/** @var integer	Sets the size of the BCC Batch Mode */
	protected $bcc_batch_size = 200;

	/** @var boolean	Automatically generate a multipart message if only html or text is given. * */
	protected $send_multipart = true;

	// Variables used within the class

	/** @var string		Used to see if were in safe mode or not. */
	protected $safe_mode = false;

	/** @var string		Contains the message to be sent in the email after compiling */
	protected $_message = '';

	/** @var string		Contains the headers to be sent in the email after compiling */
	protected $_headers = '';

	/** @var array		A list of priorities */
	protected $_priorities = array(
		'1 (Highest)',
		'2 (High)',
		'3 (Normal)',
		'4 (Low)',
		'5 (Lowest)'
	);

	/** @var array		A list of mime types loaded from application/config/mimes.php */
	protected $_mimes = array();

	/** @var array		Determines how headers are compiled when running BCC batch mode. */
	protected $_bcc_batch_running = false;

	public function __construct($config = array())
	{
		$this->smtp_vars['auth'] = (!empty($this->smtp_vars['user']) && !empty($this->smtp_vars['pass'])) ? FALSE : TRUE;
		$this->safe_mode = ((boolean) @ini_get("safe_mode") === FALSE) ? FALSE : TRUE;

		$this->init($config);

		// See if our mimes have been loaded.
		if (count($this->_mimes) == 0)
		{
			// Load the mimes!
			$this->_mimes = Config::load('mimes');
		}
	}

	/**
	 * Used to set class information.
	 *
	 * @param	array	$config		An array of configuration settings.
	 */
	public function init($config = array())
	{
		// Go through each config options and set it.
		foreach ($config AS $name => $value)
		{
			if ( ! empty($name) || !empty($value))
			{
				switch ($name)
				{
					case 'useragent': $this->useragent = (string) $value;
						break;
					case 'protocol': $this->protocol = (string) $value;
						break;
					case 'smtp_host': $this->smtp_vars['host'] = (string) $value;
						break;
					case 'sendmail_path': $this->sendmail_path = (string) $value;
						break;
					case 'smtp_user': $this->smtp_vars['user'] = (string) $value;
						break;
					case 'smtp_pass': $this->smtp_vars['pass'] = (string) $value;
						break;
					case 'smtp_port': $this->smtp_vars['port'] = (int) $value;
						break;
					case 'smtp_timeout': $this->smtp_vars['timeout'] = (int) $value;
						break;
					case 'wordwrap': $this->wordwrap = (bool) $value;
						break;
					case 'wrapchars': $this->wordwrap_width = (int) $value;
						break;
					case 'mailtype': $this->mailtype = (string) $value;
						break;
					case 'charset': $this->charset = (string) $value;
						break;
					case 'validate': $this->validity_check = (bool) $value;
						break;
					case 'priority': $this->priority = (int) $value;
						break;
					case 'crlf': $this->crlf = (string) $value;
						break;
					case 'newline': $this->newline = (string) $value;
						break;
					case 'bcc_batch_mode': $this->bcc_batch_mode = (int) $value;
						break;
					case 'bcc_batch_size': $this->bcc_batch_size = (int) $value;
						break;
					case 'force_multipart': $this->send_multipart = (bool) $value;
				}
			}
		}
		$this->smtp_vars['auth'] = (!empty($this->smtp_vars['user']) && !empty($this->smtp_vars['pass']));
		return $this;
	}

	/**
	 * Adds a direct recipient
	 *
	 * @param	string	$address	A single email, a comma seperated list of emails, or an array of emails
	 * @return	Email_Driver
	 */
	public function to($address)
	{
		$this->_add_recipient('to', func_get_args());
		return $this;
	}

	/**
	 * Adds a carbon copy recipient
	 *
	 * @param	string	$address	A single email, a comma seperated list of emails, or an array of emails
	 * @return	Email_Driver
	 */
	public function cc($address)
	{
		$this->_add_recipient('cc', func_get_args());
		return $this;
	}

	/**
	 * Adds a blind carbon copy recipient
	 *
	 * @param	string	$address	A single email, a comma seperated list of emails, or an array of emails
	 * @return	Email_Driver
	 */
	public function bcc($address)
	{
		$this->_add_recipient('bcc', func_get_args());
		return $this;
	}

	/**
	 * Sets the senders email address
	 *
	 * @param	string	$address	The email address of the sender.
	 * @return	Email_Driver
	 */
	public function from($address, $name = '')
	{
		if ( ! empty($name))
		{
			$address = $name.' <'.$address.'>';
		}
		$this->sender = $address;
		return $this;
	}

	/**
	 * Sets the subject of the email.
	 *
	 * @param	string	$subject	The subject of the email.
	 * @return	Email_Driver
	 */
	public function subject($subject)
	{
		$this->subject = $subject;
		return $this;
	}

	/**
	 * Sets a header for the email.
	 *
	 * @param	string	$index	The name of the header
	 * @param	string	$value	The value of the header
	 * @return	Email_Driver
	 */
	public function set_header($index, $value, $override = true)
	{
		if (($override || empty($this->headers[$index])) && !empty($index) && !empty($value))
			$this->headers[$index] = $value;
		return $this;
	}

	/**
	 * Sets the message of the email, content type is determined by 'mailtype'
	 *
	 * @param	string	$content
	 * @return	Email_Driver
	 */
	public function message($content)
	{
		if ($this->mailtype == 'html')
		{
			$this->html($content);
		}
		else
		{
			$this->text($content);
		}
		return $this;
	}

	/**
	 * Sets the alternative message for the email. HTML if 'mailtype' is Plain Text, and viceversa.
	 *
	 * @param	string	$content
	 * @return	Email_Driver
	 */
	public function set_alt_message($content)
	{
		if ($this->mailtype != 'html')
		{
			$this->html($content);
		}
		else
		{
			$this->text($content);
		}
		return $this;
	}

	/**
	 * Sets the HTML content to place into the email.
	 *
	 * @param	string	$html
	 * @return	Email_Driver
	 */
	public function html($html)
	{
		$this->html_contents = $html;
		return $this;
	}

	/**
	 * Sets the Plain Text content to place into the email.
	 *
	 * @param	string	$text
	 * @return	Email_Driver
	 */
	public function text($text)
	{
		$this->text_contents = $text;
		return $this;
	}

	/**
	 * Sends the email.
	 *
	 * @return	boolean		True if success, false if failure.
	 */
	public function send()
	{
		$return = true;
		// Set all our processing vars except message and header
		$this->_message = $this->_compile_message();
		// Headers always compiled last!
		$this->_headers = $this->_compile_headers();
		$this->_debug_message('Using protocol '.$this->protocol.' for sending.', 'info');
		$protocol = 'Email_'.ucfirst($this->protocol);
		if (true)
		{
			$protocol = new $protocol($this);
			if ($this->bcc_batch_mode && count($this->bcc_recipients) > $this->bcc_batch_size)
			{
				$count = count($this->bcc_recipients);
				$this->_debug_message("BCC Batch mode running...");
				$offset = 0;
				$origbcc = $this->bcc_recipients;
				$this->_bcc_batch_running = false;
				while ($offset < $count && $return)
				{ // TODO: Add to codeigniter version
					// Loop while we still have batches of blind carbon copies to send.
					// Note that the first run outputs any to and cc addresses also.
					$length = $count >= $offset + $this->bcc_batch_size ? $this->bcc_batch_size : $count % $this->bcc_batch_size;
					$this->bcc_recipients = array_slice($origbcc, $offset, $length);
					$this->_debug_message('BCC header: '.implode(', ', $this->bcc_recipients));
					$return = $return && $this->_send();
					$offset += $this->bcc_batch_size;
					$this->_bcc_batch_running = true;
				}
				// Reset BCC information.
				$this->bcc_recipients = $origbcc;
				$this->_bcc_batch_running = false;
			}
			else
			{
				// Send using the normal methods.
				$return = $this->_send();
			}
		}
		else
		{
			$return = false;
			$this->_debug_message('Protocol '.$this->protocol.' is not valid. Use mail, sendmail, or smtp.', 'error');
		}
		// Debug a message about its status.
		if ($return)
		{
			$this->_debug_message('Message was successfully sent using '.$this->protocol.'.', 'info');
		}
		else
		{
			$this->_debug_message('Message was not successfully send using '.$this->protocol.'.', 'info');
		}
		return $return;
	}

	/**
	 * Used by drivers to do custome commands for sending.
	 *
	 * @return boolean	True if successful, false if not.
	 */
	protected abstract function _send();

	/**
	 * Attaches a file in the local filesystem to the email.
	 *
	 * @param	string	$filename		The file to be used.
	 * @param	string	$disposition	Defaults to attachment, can also be inline?
	 */
	public function attach($filename, $disposition = 'attachment')
	{
		$ftype = next(explode('.', basename($filename)));
		// If mime type was not determined, send it is application/octet-stream.
		$mime = isset($this->_mimes[$ftype]) ? $this->_mimes[$ftype] : 'application/octet-stream';
		$this->attachments[] = array(
			'contents' => '',
			'filename' => $filename,
			'filetype' => $mime,
			'disposition' => $disposition,
			'dynamic' => false
		);
		return $this;
	}

	/**
	 * Dynamically attaches a file to the email.
	 *
	 * @param	string	$contents		The contents of the attachment
	 * @param	string	$filename		The filename to use in the email
	 * @param	string	$disposition	Defaults to attachment, can also be inline?
	 */
	public function dynamic_attach($contents, $filename, $disposition = 'attachment')
	{
		$ftype = next(explode('.', basename($filename)));
		// If mime type was not determined, send it is application/octet-stream.
		$mime = isset($this->_mimes[$ftype]) ? $this->_mimes[$ftype] : 'application/octet-stream';
		$this->attachments[] = array(
			'contents' => $contents,
			'filename' => $filename,
			'filetype' => $mime,
			'disposition' => $disposition,
			'dynamic' => true
		);
		return $this;
	}

	/**
	 * Email validation from the valid_email method of Codeigniters Email Class.
	 *
	 * @author	CodeIgniter
	 * @link	http://codeigniter.com/
	 * @param	string	$address The email address to check for validity
	 */
	protected function _valid_email($address)
	{
		// Instead of checking if validity is to be checked elsewhere, check it here :)
		return ($this->validity_check && !preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $address)) ? FALSE : TRUE;
	}

	/**
	 * Adds a recipient to the email.
	 *
	 * @param	string	$address	Either a string, comma seperated string, or an array is accepted
	 */
	protected function _add_recipient($type, $args)
	{
		$additions = array();
		foreach ($args AS $arg)
		{
			// If is a string, make it an array
			if (is_string($arg))
			{
				$arg = explode(',', $arg);
			}
			// Check that it is an array :)
			if (is_array($arg))
			{
				$additions = array_merge($additions, $arg);
			}
		}
		// Now decide which variable to place it in
		switch ($type)
		{
			case 'to':
				$this->recipients = array_merge($this->recipients, $additions);
				break;
			case 'cc':
				$this->cc_recipients = array_merge($this->cc_recipients, $additions);
				break;
			case 'bcc':
				$this->bcc_recipients = array_merge($this->bcc_recipients, $additions);
				break;
		}
		return $this;
	}

	/**
	 * Generated a message ID for the email. Base on the _get_message_id() method from Codeigniters Email class.
	 *
	 * @author	CodeIgniter
	 * @link	http://codeigniter.com/
	 * @return	string The message ID for the message.
	 */
	protected function _get_message_id()
	{
		$from = $this->sender;
		$from = str_replace(">", "", $from);
		$from = str_replace("<", "", $from);

		return "<".uniqid('').strrchr($from, '@').">";
	}

	// Following functions are called to create the email

	/**
	 * Compiles the message to be sent.
	 *
	 * @return	string	The message.
	 */
	protected function _compile_message()
	{
		$return = false;
		// First off create alternative content if requested.
		$this->_compile_alt_message();
		$htmlCheck = !empty($this->html_contents);
		$textCheck = !empty($this->text_contents);
		$attachCheck = count($this->attachments) > 0;
		// Get how many parts to this email
		$parts = ($htmlCheck ? 1 : 0) + ($textCheck ? 1 : 0) + count($this->attachments);
		if ($parts > 1)
		{
			// Multipart email coming right up!
			// Generate a boundary for the message
			$boundary = 'email_boundary_'.md5(time() * microtime());
			// Set our headers
			$this->set_header('Content-Type', 'multipart/alternative; boundary="'.$boundary.'"');
			// Create a little warning for older email clients
			$return = $this->newline."Multipart emails may not work on your client.".$this->newline.$this->newline;
			if ($textCheck)
			{
				// Create the text part of the message
				$return .= "--".$boundary.$this->newline;  // Boundary
				$return .= "Content-Type: text/plain; charset=".$this->charset.$this->newline;
				$return .= "Content-Transfer-Encoding: ".$this->encoding.$this->newline;
				$return .= $this->newline.$this->_prep_quoted_printable($this->_word_wrap($this->text_contents)).$this->newline.$this->newline;
			}
			if ($htmlCheck)
			{
				// Create the text part of the message
				$return .= "--".$boundary.$this->newline;  // Boundary
				$return .= "Content-Type: text/html; charset=".$this->charset.$this->newline;
				$return .= "Content-Transfer-Encoding: ".$this->encoding.$this->newline;
				$return .= $this->newline.$this->_prep_quoted_printable($this->_word_wrap($this->html_contents)).$this->newline.$this->newline;
			}
			if ($attachCheck)
			{
				foreach ($this->attachments AS $attachment)
				{
					$contents = '';
					$basename = '';
					if ($attachment['dynamic'] == true)
					{
						// TODO: Dynamic attachment handling
						$basename = $attachment['filename'];
						$contents = $attachment['contents'];
					}
					else
					{
						// TODO: File attachment handling
						$filename = $attachment['filename'];
						$basename = basename($filename);
						if ( ! file_exists($filename))
						{
							$this->_debug_message('Could not find the file '.$filename, 'warning');
						}
						else
						{
							$filesize = filesize($filename) + 1;
							if ( ! $fp = fopen($filename, FOPEN_READ))
							{
								$this->_debug_message('Could not read the file '.$filename, 'warning');
							}
							else
							{
								$contents = fread($fp, $filesize);
								fclose($fp);
							}
						}
					}
					if ( ! empty($contents))
					{
						$filename = $attachment['filename'];
						$filetype = is_array($attachment['filetype']) ? $attachment['filetype'][0] : $attachment['filetype'];
						// Create the headers
						$return .= "--".$boundary.$this->newline;  // Boundary
						$return .= "Content-Type: ".$filetype."; name=\"$basename\"".$this->newline;
						$return .= "Content-Disposition: ".$attachment['disposition'].";".$this->newline;
						$return .= "Content-Transfer-Encoding: base64".$this->newline.$this->newline;
						$return .= chunk_split(base64_encode($contents)).$this->newline.$this->newline;
					}
				}
			}
			$return .= "--".$boundary."--".$this->newline;
		}
		else if ($textCheck || $htmlCheck)
		{
			$return = $this->newline;
			$this->set_header("Content-Transfer-Encoding", $this->encoding);
			if ($textCheck)
			{
				$this->set_header('Content-Type', 'text/plain');
				$return .= $this->_prep_quoted_printable($this->_word_wrap($this->text_contents));
			}
			else
			{
				$this->set_header('Content-Type', 'text/html');
				$return .= $this->_prep_quoted_printable($this->_word_wrap($this->html_contents));
			}
		}
		else
		{
			$this->_debug_message('No HTML or Plain Text message was defined.', 'warning');
		}
		return $return;
	}

	/**
	 * Compiles the headers to be sent in the email.
	 *
	 * @return	string	The headers.
	 */
	protected function _compile_headers($for_debug = false)
	{
		// Setup out return variable
		$return = '';
		// Set the from, carbon ccopy, and blind carbon copy fields
		$this->set_header('From', $this->_format_email($this->sender));
		// Set the subject, prepare the subject.
		$this->set_header('Subject', $this->_prep_q_encoding($this->subject));
		// Set the MessageID.
		$this->set_header('Message-ID', $this->_get_message_id());
		// These will be set incase they were not defined by the user.
		$this->set_header('Date', date('r'), false);
		$this->set_header('Mime-Version', '1.0', false);
		$this->set_header('X-Sender', $this->_format_email($this->sender), false);
		$this->set_header('X-Mailer', $this->useragent, false);
		$this->set_header('User-Agent', $this->useragent, false);
		$this->set_header('X-Priority', $this->_priorities[$this->priority - 1], false);
		// Set the to header after sanitizing the emails
		if ( ! $this->_bcc_batch_running)
		{
			$this->set_header('To', implode(', ', $this->_sanitize_emails($this->recipients)));
			if (count($this->cc_recipients) > 0)
				$this->set_header('CC', implode(', ', $this->cc_recipients));
		}
		if (count($this->bcc_recipients) > 0)
			$this->set_header('BCC', implode(', ', $this->bcc_recipients));
		foreach ($this->headers AS $name => $value)
		{
			$return .= $name.': '.$value.$this->newline;
		}
		return $return;
	}

	// Following functions are used to modify data

	/**
	 * Used to clean out the $recipients, $cc_recipients, and $bcc_recipients variables.
	 *
	 * @param	array|string	$email	An array of emails or a single email
	 * @return	array|string	An array of emails or a single email.
	 */
	protected function _sanitize_emails($emails)
	{
		$return = '';
		if ( ! is_array($emails))
		{
			if ($this->_valid_email($this->_clean_email($emails)))
			{
				$return = $this->_format_email($emails);
			}
		}
		else
		{
			$return = array();
			for ($i = 0; $i < count($emails); $i++)
			{
				if ($this->_valid_email($this->_clean_email($emails[$i])))
				{
					$return[] = $this->_format_email($emails[$i]);
				}
			}
		}
		return $return;
	}

	/**
	 * Takes an email and formats it for use in the headers.
	 *
	 * @param	string	$address	The email address to format
	 * @return	string	The formatted email address
	 */
	protected function _format_email($address)
	{
		$return = '';
		$name = '';
		if (preg_match('#^(.*?) ?<(.*?)>$#', $address, $match) == 1)
		{
			$address = $match[2];
			$name = $match[1];
		}
		// prepare the display name
		if ($name != '')
		{
			// only use Q encoding if there are characters that would require it
			if ( ! preg_match('/[\200-\377]/', $name))
			{
				// add slashes for non-printing characters, slashes, and double quotes, and surround it in double quotes
				$return = '"'.addcslashes($name, "\0..\37\177'\"\\").'" <'.$address.'>';
			}
			else
			{
				$return = $this->_prep_q_encoding($name, TRUE).' <'.$address.'>';
			}
		}
		else
		{
			$return = $address;
		}
		return $return;
	}

	/**
	 * Email cleansing from the clean_email method of Codeigniters Email Class
	 *
	 * @author	CodeIgniter
	 * @link	http://codeigniter.com/
	 * @param	array|string $email Either an array of emails or just a single email
	 */
	protected function _clean_email($email)
	{
		$return = null;
		if ( ! is_array($email))
		{
			if (preg_match('/\<(.*)\>/', $email, $match))
			{
				$return = $match['1'];
			}
			else
			{
				$return = $email;
			}
		}
		else
		{
			$return = array();
			foreach ($email as $addy)
			{
				if (preg_match('/\<(.*)\>/', $addy, $match))
				{
					$return[] = $match['1'];
				}
				else
				{
					$return[] = $addy;
				}
			}
		}
		return $return;
	}

	/**
	 * Build alternative plain text message
	 *
	 * This function provides the raw message for use in plain-text headers of HTML-formatted emails.
	 * If the user hasn't specified his own alternative message it creates one by stripping the HTML
	 *
	 * Based off the _get_alt_message() method of Codeigniters Email Class
	 *
	 * @author	CodeIgniter
	 * @link	http://codeigniter.com/
	 */

	function _compile_alt_message()
	{
		$htmlCheck = !empty($this->html_contents) && empty($this->text_contents) && $this->send_multipart;
		$textCheck = empty($this->html_contents) && !empty($this->text_contents) && $this->send_multipart;
		if ($htmlCheck)
		{
			// Check if the html message is wrapped in a body...
			$content = preg_replace("/.*<body>(.*)<\/body>.*/is", "$1", $this->html_contents);
			$content = trim(strip_tags($content));
			$content = str_replace("\t", "", $content);
			$content = preg_replace(
							array('#<!--(.*)--\>#', '#((?:[\\r\\n]|[\\n]){2,})#'),
							array("", $this->newline.$this->newline),
							$content
			);
			$this->text($content);
		}
		if ($textCheck)
		{
			// Wrap a simple html, head and body around it.
			$content = "<html>".$this->newline;
			$content .= "<head><title>".$this->subject."</title></head>".$this->newline;
			$content .= "<body>".$this->newline;
			$content .= nl2br($this->text_contents).$this->newline;
			$content .= "</body>".$this->newline;
			$content .= "</html>".$this->newline;
			$this->html($content);
		}
	}

	/**
	 * Prepares string for Quoted-Printable Content-Transfer-Encoding.
	 * Refer to RFC 2045 http://www.ietf.org/rfc/rfc2045.txt
	 *
	 * From the prep_quoted_printable method of Codeigniters Email Class
	 *
	 * @author	CodeIgniter
	 * @link	http://codeigniter.com/
	 * @return	string
	 */
	protected function _prep_quoted_printable($str, $charlim = '')
	{
		// Set the character limit
		// Don't allow over 76, as that will make servers and MUAs barf
		// all over quoted-printable data
		if ($charlim == '' OR $charlim > '76')
		{
			$charlim = '76';
		}

		// Reduce multiple spaces
		$str = preg_replace("| +|", " ", $str);

		// kill nulls
		$str = preg_replace('/\x00+/', '', $str);

		// Standardize newlines
		if (strpos($str, "\r") !== FALSE)
		{
			$str = str_replace(array("\r\n", "\r"), "\n", $str);
		}

		// We are intentionally wrapping so mail servers will encode characters
		// properly and MUAs will behave, so {unwrap} must go!
		$str = str_replace(array('{unwrap}', '{/unwrap}'), '', $str);

		// Break into an array of lines
		$lines = explode("\n", $str);

		$escape = '=';
		$output = '';

		foreach ($lines as $line)
		{
			$length = strlen($line);
			$temp = '';

			// Loop through each character in the line to add soft-wrap
			// characters at the end of a line " =\r\n" and add the newly
			// processed line(s) to the output (see comment on $crlf class property)
			for ($i = 0; $i < $length; $i++)
			{
				// Grab the next character
				$char = substr($line, $i, 1);
				$ascii = ord($char);

				// Convert spaces and tabs but only if it's the end of the line
				if ($i == ($length - 1))
				{
					$char = ($ascii == '32' OR $ascii == '9') ? $escape.sprintf('%02s', dechex($ascii)) : $char;
				}

				// encode = signs
				if ($ascii == '61')
				{
					$char = $escape.strtoupper(sprintf('%02s', dechex($ascii)));  // =3D
				}

				// If we're at the character limit, add the line to the output,
				// reset our temp variable, and keep on chuggin'
				if ((strlen($temp) + strlen($char)) >= $charlim)
				{
					$output .= $temp.$escape.$this->crlf;
					$temp = '';
				}

				// Add the character to our temporary line
				$temp .= $char;
			}

			// Add our completed line to the output
			$output .= $temp.$this->crlf;
		}

		// get rid of extra CRLF tacked onto the end
		$output = substr($output, 0, strlen($this->crlf) * -1);

		return $output;
	}

	/**
	 * Performs "Q Encoding" on a string for use in email headers.  It's related
	 * but not identical to quoted-printable, so it has its own method
	 *
	 * From the prep_q_encoding method of Codeigniters Email Class
	 *
	 * @author	CodeIgniter
	 * @link	http://codeigniter.com/
	 * @return	string
	 */
	protected function _prep_q_encoding($str, $from = FALSE)
	{
		$str = str_replace(array("\r", "\n"), array('', ''), $str);

		// Line length must not exceed 76 characters, so we adjust for
		// a space, 7 extra characters =??Q??= , and the charset that we will add to each line
		$limit = 75 - 7 - strlen($this->charset);

		// these special characters must be converted too
		$convert = array('_', '=', '?');

		if ($from === TRUE)
		{
			$convert[] = ',';
			$convert[] = ';';
		}

		$output = '';
		$temp = '';

		for ($i = 0, $length = strlen($str); $i < $length; $i++)
		{
			// Grab the next character
			$char = substr($str, $i, 1);
			$ascii = ord($char);

			// convert ALL non-printable ASCII characters and our specials
			if ($ascii < 32 OR $ascii > 126 OR in_array($char, $convert))
			{
				$char = '='.dechex($ascii);
			}

			// handle regular spaces a bit more compactly than =20
			if ($ascii == 32)
			{
				$char = '_';
			}

			// If we're at the character limit, add the line to the output,
			// reset our temp variable, and keep on chuggin'
			if ((strlen($temp) + strlen($char)) >= $limit)
			{
				$output .= $temp.$this->crlf;
				$temp = '';
			}

			// Add the character to our temporary line
			$temp .= $char;
		}

		$str = $output.$temp;

		// wrap each line with the shebang, charset, and transfer encoding
		// the preceding space on successive lines is required for header "folding"
		$str = trim(preg_replace('/^(.*)$/m', ' =?'.$this->charset.'?Q?$1?=', $str));

		return $str;
	}

	/**
	 * Used to apply word wrapping to messages, from the word_wrap method of Codeigniters Email Class
	 *
	 * @author	CodeIgniter
	 * @link	http://codeigniter.com/
	 * @param	string	$str The string of text to wordwrap
	 * @return	string	The wordwrapped string.
	 */
	protected function _word_wrap($str)
	{
		$output = '';
		if ($this->wordwrap)
		{
			// Se the character limit
			$charlim = empty($this->wordwrap_width) ? "76" : $this->wordwrap_width;

			// Reduce multiple spaces
			$str = preg_replace("| +|", " ", $str);

			// Standardize newlines
			if (strpos($str, "\r") !== FALSE)
			{
				$str = str_replace(array("\r\n", "\r"), "\n", $str);
			}

			// If the current word is surrounded by {unwrap} tags we'll
			// strip the entire chunk and replace it with a marker.
			$unwrap = array();
			if (preg_match_all("|(\{unwrap\}.+?\{/unwrap\})|s", $str, $matches))
			{
				for ($i = 0; $i < count($matches['0']); $i++)
				{
					$unwrap[] = $matches['1'][$i];
					$str = str_replace($matches['1'][$i], "{{unwrapped".$i."}}", $str);
				}
			}

			// Use PHP's native function to do the initial wordwrap.
			// We set the cut flag to FALSE so that any individual words that are
			// too long get left alone.  In the next step we'll deal with them.
			$str = wordwrap($str, $charlim, "\n", FALSE);

			// Split the string into individual lines of text and cycle through them
			$output = "";
			foreach (explode("\n", $str) as $line)
			{
				// Is the line within the allowed character count?
				// If so we'll join it to the output and continue
				if (strlen($line) <= $charlim)
				{
					$output .= $line.$this->newline;
					continue;
				}

				$temp = '';
				while ((strlen($line)) > $charlim)
				{
					// If the over-length word is a URL we won't wrap it
					if (preg_match("!\[url.+\]|://|wwww.!", $line))
					{
						break;
					}

					// Trim the word down
					$temp .= substr($line, 0, $charlim - 1);
					$line = substr($line, $charlim - 1);
				}

				// If $temp contains data it means we had to split up an over-length
				// word into smaller chunks so we'll add it back to our current line
				if ($temp != '')
				{
					$output .= $temp.$this->newline.$line;
				}
				else
				{
					$output .= $line;
				}

				$output .= $this->newline;
			}

			// Put our markers back
			if (count($unwrap) > 0)
			{
				foreach ($unwrap as $key => $val)
				{
					$output = str_replace("{{unwrapped".$key."}}", $val, $output);
				}
			}
		}
		else
		{
			$output = $str;
		}
		return $output;
	}

	// Following variables and functions are used soley for debuging

	protected $_debug = array();

	/**
	 * Prints the debugger.
	 */
	public function print_debugger()
	{
		$message = "<div style='font-family: Monospace;'>\n<strong>Dmail Class Log:</strong><br />\n";
		foreach ($this->_debug AS $debug)
		{
			$message .= preg_replace(array('#<#', '#>#', "#\t#"), array('&lt;', '&gt;', '&nbsp;&nbsp;&nbsp;'),
							"[".date("H:i:s", $debug['timestamp']).".".$debug['microtime']."] ".
							$debug['severity'].": ".$debug['message']
					)."<br />\n";
		}
		$message .= "<br />\n<strong>Sent emails source:</strong><br />\n";
		$message .= nl2br(preg_replace(array('#<#', '#>#', "#\t#"), array('&lt;', '&gt;', '&nbsp;&nbsp;&nbsp;'), $this->_compile_headers(true).$this->_message));
		$message .= "</div>";
		echo $message;
	}

	/**
	 * Sets a debug message.
	 *
	 * @param	string	$message	The debug message.
	 * @param	string	$severity	The severity of the message. Info/Debug, Warning, Error.
	 */
	protected function _debug_message($message, $severity = 'info')
	{
		$microtime = microtime();
		$microtime = substr($microtime, 2, 4);
		$this->_debug[] = array(
			'severity' => $severity,
			'message' => $message,
			'timestamp' => time(),
			'microtime' => $microtime
		);
	}

}
