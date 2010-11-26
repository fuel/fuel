<?php

namespace Fuel;

/**
 * Class used to create instances of the email driver.
 */
class Email {

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


}
