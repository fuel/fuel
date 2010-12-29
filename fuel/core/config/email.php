<?php
/**
 * Email config for use with the email package.
 */

return array(

	/* The user agent to use in the email headers (User-Agent and X-Mailer). Mostly used for aesthetics.
	 * Value is a string to use as the useragent.
	 * Defaults to 'FuelPHP'
	 */
	// 'useragent' => 'FuelPHP',

	/* Sets the protocol to use. Currently mail, sendmail, and smtp are supported.
	 * Value can be any supported protocol, currently 'mail', 'sendmail', and 'smtp'
	 * Defaults to 'mail'
	 */
	// 'protocol' => 'mail',

	/* Sets the mailpath for sendmail. Defaults to /usr/bin/sendmail
	 * Value is the location of the sendmail executable.
	 * Defaults to '/usr/bin/sendmail'
	 */
	// 'mailpath' => '/usr/bin/sendmail',

	/* Sets the SMTP host to use when mailing through SMTP.
	 * Value is the hostname of the SMTP server you are trying to connect to.
	 * Default to an empty string.
	 */
	// 'smtp_host' => '',

	/* Sets the SMTP user if authentication is needed for the SMTP protocol.
	 * Value is the username of the account used to connect to SMTP, or an empty string to disable authentication.
	 * Defaults to an empty string.
	 */
	// 'smtp_user' => '',

	/* Sets the SMTP password if authentication is needed for the SMTP protocol.
	 * Value is the password tied to your smtp user account, or an empty string to disable authentication.
	 * Defaults to an empty string.
	 */
	// 'smtp_pass' => '',

	/* Sets the SMTP port to use for the SMTP protocol.
	 * Value can be any integer between 1 and 65535
	 * Defaults to 25
	 */
	// 'smtp_port' => 25,

	/* Sets the SMTP timeout to use for the SMTP protocol.
	 * Value can be any integer greater than 0
	 * Deafults to 5
	 */
	// 'smtp_timeout' => 5,

	/* Enables or disables the wordwrapping of messages.
	 * Value can be true (standards compilent) or false
	 * Defaults to true
	 */
	// wordwrap' => true,

	/* Sets the max length of each line when wordwrapping.
	 * Value can be any positive integer
	 * Defaults to 76
	 */
	// 'wrapchars' => 76,

	/* Sets the type of mail to be sent using the message function.
	 * Value can be 'html' or 'text'
	 * Defaults to 'text'
	 */
	// 'mailtype' => 'text',

	/* Sets the charset to use in the HTML and Plain Text messages.
	 * Value can be any valid charset
	 * Defaults to 'utf-8'
	 */
	// 'charset' => 'utf-8',

	/* Enables or disables email validation, which will remove emails which don't pass validation.
	 * Does not support localhost.
	 * Value can be true for enabled, or false for disabled.
	 * Defaults to false.
	 */
	// 'validate' => false,

	/* Sets the priority of the email.
	 * Value can be 1 for highest priority to 5 for lowest priority
	 * Defaults to 3 (Normal priority)
	 */
	// 'priority' => 3,

	/* crlf and newline defines the newline characters to use.
	 * Value can be:
	 *		"\n"	is used for compatability with some server that don't support \r\n newlines
	 *		"\r\n"	is the standard compilant way to make new lines.
	 * Defaults to "\n"
	 */
	// 'crlf' => "\n",
	// 'newline' => "\n",

	/* Enables or disables BCC Batch Mode.
	 * Value can be true to enable, or false to disable
	 * Defaults to false
	 */
	// 'bcc_batch_mode' => false,

	/* Used to set how many emails get sent each round if bcc_batch_mode is enabled.
	 * Value canan be any integer greater than 0.
	 * Defaults to 200
	 */
	// bcc_batch_size' => 200,

	/* Used to force a multipart message when sending html or text messages.
	 * Value can be true to enable, or false to disable
	 * Defaults to true
	 */
	// 'force_multipart' => true
);

/* End of file email.php */