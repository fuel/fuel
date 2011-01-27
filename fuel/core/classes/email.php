<?php

/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * Class used to server up the email
 *
 * @package		Fuel
 * @version		1.0
 * @author		DudeAmI aka Kris <dudeami0@gmail.com>
 * @license		MIT License
 * @copyright	2010 - 2011 Fuel Development Team
 * @link		http://fuelphp.com
 */

namespace Fuel\Core;



class Email {

	protected static $_instance = null;

	public static function instance()
	{
		if (static::$_instance == null)
		{
			static::$_instance = static::factory();
		}
		return static::$_instance;
	}

	/**
	 * Creates a new instance of the email driver
	 *
	 * @param	array	$config
	 */
	public static function factory($config = array())
	{
		$initconfig = Config::load('email');

		if (is_array($config) && is_array($initconfig))
		{
			$config = array_merge($initconfig, $config);
		}

		$protocol = ucfirst( ! empty($config['protocol']) ? $config['protocol'] : 'mail');
		$class = 'Email_' . $protocol;
		if ($protocol == 'Driver' || ! class_exists($class))
		{
			throw new \Fuel_Exception('Protocol ' . $protocol . ' is not a valid protocol for emailing.');
		}
		return new $class($config);
	}

	/**
	 * Used to set class information.
	 *
	 * @param	array	$config		An array of configuration settings.
	 */
	public function init($config = array())
	{
		return static::instance()->init($config);
	}

	/**
	 * Adds a direct recipient
	 *
	 * @param	string	$address	A single email, a comma seperated list of emails, or an array of emails
	 * @return	Email_Driver
	 */
	public static function to($address)
	{
		return static::instance()->to($address);
	}

	/**
	 * Adds a carbon copy recipient
	 *
	 * @param	string	$address	A single email, a comma seperated list of emails, or an array of emails
	 * @return	Email_Driver
	 */
	public static function cc($address)
	{
		return static::instance()->cc($address);
	}

	/**
	 * Adds a blind carbon copy recipient
	 *
	 * @param	string	$address	A single email, a comma seperated list of emails, or an array of emails
	 * @return	Email_Driver
	 */
	public static function bcc($address)
	{
		return static::instance()->bcc($address);
	}

	/**
	 * Sets the subject of the email.
	 *
	 * @param	string	$subject	The subject of the email.
	 * @return	Email_Driver
	 */
	public static function from($address, $name = '')
	{
		return static::instance()->from($address, $name);
	}

	/**
	 * Sets the subject of the email.
	 *
	 * @param	string	$subject
	 * @return	Email_Driver
	 */
	public static function subject($subject)
	{
		return static::instance()->subject($subject);
	}

	/**
	 * Sets a header for the email.
	 * @param	string		$index		The name of the header
	 * @param	string		$value		The value of the header
	 * @param	boolean		$override	Decides if it should write over existing headers or not.
	 */
	public static function set_header($index, $value, $override = true)
	{
		return static::instance()->set_header($index, $value, $override);
	}

	/**
	 * Sets the message of the email, content type is determined by 'mailtype'
	 *
	 * @param	string	$content
	 * @return	Email_Driver
	 */
	public static function message($content)
	{
		return static::instance()->message($content);
	}

	/**
	 * Sets the alternative message for the email. HTML if 'mailtype' is Plain Text, and viceversa.
	 *
	 * @param	string	$content
	 * @return	Email_Driver
	 */
	public static function set_alt_message($content)
	{
		return static::instance()->set_alt_message($content);
	}

	/**
	 * Sets the HTML content to place into the email.
	 *
	 * @param	string	$html	The emails HTML
	 * @return	Email_Driver
	 */
	public static function html($html)
	{
		return static::instance()->html($html);
	}

	/**
	 * Sets the Plain Text content to place into the email.
	 *
	 * @param	string	$html	The emails Plain Text
	 * @return	Email_Driver
	 */
	public static function text($text)
	{
		return static::instance()->text($text);
	}

	/**
	 * Sends the email.
	 *
	 * @return	boolean		True if success, false if failure.
	 */
	public static function send()
	{
		return static::instance()->send();
	}

	/**
	 * Attaches a file in the local filesystem to the email.
	 *
	 * @param	string	$filename		The file to be used.
	 * @param	string	$disposition	Defaults to attachment, can also be inline?
	 */
	public static function attach($filename, $disposition = 'attachment')
	{
		return static::instance()->attach($filename, $disposition);
	}

	/**
	 * Dynamically attaches a file to the email.
	 *
	 * @param	string	$contents		The contents of the attachment
	 * @param	string	$filename		The filename to use in the email
	 * @param	string	$disposition	Defaults to attachment, can also be inline?
	 */
	public static function dynamic_attach($contents, $filename, $disposition = 'attachment')
	{
		return static::instance()->dynamic_attach($contents, $filename, $disposition);
	}

	/**
	 * Prints the debugger.
	 */
	public static function print_debugger()
	{
		echo static::instance()->print_debugger();
	}

}
