<?php

namespace Fuel;

/**
 * Class used to server up the email
 */
class Email {

	protected static $_instance = null;

	public static function instance() {
		if (static::$_instance == null) {
			static::$_instance = static::factory();
		}
		return static::$_instance;
	}

	/**
	 * Creates a new instance of the email driver
	 * @param <type> $config
	 */
	public static function factory($config=Array()) {
		$protocol = ucfirst(!empty($config['protocol']) ? $config['protocol'] : 'mail');
		$class = 'Email_' . $protocol;
		if ($protocol == 'Driver' || !class_exists($class)) {
			throw new Exception('Protocol ' . $protocol . ' is not a valid protocol for emailing.');
		}
		return new $class($config);
	}
	/**
	 * Adds a direct recipient
	 * @param String $address A single email, a comma seperated list of emails, or an array of emails
	 * @return Dmail
	 */
	public static function to($address) {
		return static::instance()->to($address);
	}

	/**
	 * Adds a carbon copy recipient
	 * @param String $address A single email, a comma seperated list of emails, or an array of emails
	 * @return Dmail
	 */
	public static function cc($address) {
		return static::instance()->cc($address);
	}

	/**
	 * Adds a blind carbon copy recipient
	 * @param String $address A single email, a comma seperated list of emails, or an array of emails
	 * @return Dmail
	 */
	public static function bcc($address) {
		return static::instance()->bcc($address);
	}

	/**
	 * Sets the senders email address
	 * @param String $address The email address of the sender.
	 * @return Dmail
	 */
	public static function from($address, $name='') {
		return static::instance()->from($address, $name);
	}

	/**
	 * Sets the subject of the email.
	 * @param String $subject
	 * @return Dmail
	 */
	public static function subject($subject) {
		return static::instance()->subject($subject);
	}

	/**
	 * Sets a header for the email.
	 * @param String $index The name of the header
	 * @param String $value The value of the header
	 * @param Boolean $override Decides if it should write over existing headers or not.
	 */
	public static function set_header($index, $value, $override = true) {
		return static::instance()->set_header($index, $value, $override);
	}

	/**
	 * Sets the message of the email, content type is determined by 'mailtype'
	 * @param String $content
	 * @return Dmail
	 */
	public static function message($content) {
		return static::instance()->message($content);
	}

	/**
	 * Sets the alternative message for the email. HTML if 'mailtype' is Plain Text, and viceversa.
	 * @param String $content
	 * @return Dmail
	 */
	public static function set_alt_message($content) {
		return static::instance()->set_alt_message($content);
	}

	/**
	 * Sets the HTML content to place into the email.
	 * @param String $html The emails HTML
	 * @return Dmail
	 */
	public static function html($html) {
		return static::instance()->html($html);
	}

	/**
	 * Sets the Plain Text content to place into the email.
	 * @param String $html The emails Plain Text
	 * @return Dmail
	 */
	public static function text($text) {
		return static::instance()->text($text);
	}

	/**
	 * Sends the email.
	 * @return boolean True if success, false if failure.
	 */
	public static function send() {
		return static::instance()->send();
	}

	/**
	 * Attaches a file in the local filesystem to the email.
	 * @param String $filename The file to be used.
	 * @param String $disposition Defaults to attachment, can also be inline?
	 */
	public static function attach($filename, $disposition = 'attachment') {
		return static::instance()->attach($filename, $disposition);
	}

	/**
	 * Dynamically attaches a file to the email.
	 * @param String $contents The contents of the attachment
	 * @param String $filename The filename to use in the email
	 * @param String $disposition Defaults to attachment, can also be inline?
	 */
	public static function dynamic_attach($contents, $filename, $disposition = 'attachment') {
		return static::instance()->dynamic_attach($contents, $filename, $disposition);
	}

	/**
	 * Prints the debugger.
	 */
	public static function print_debugger() {
		static::instance()->print_debugger();
	}

}
