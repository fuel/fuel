<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Harro "WanWizard" Verton
 * @license		MIT License
 * @copyright	2010 - 2011 Fuel Development Team
 * @link		http://fuelphp.com
 */

return array(

	/**
	 * Encryption salt.
	 *
	 * Make sure to update this to something completely random!!!!
	 */
	'salt'			=> 'sup3rs3Cr3tk3y564',

	/**
	 * Indicate if you want to use the MCRYPT libaries if available.
	 *
	 * Note that if your encrypted data has to be portable, set this to false
	 * unless you know all target platforms have mcrypt available too
	 */
	'use_mcrypt'	=> true,

	/**
	 * MCRYPT cipher to use
	 *
	 * See http://www.php.net/manual/en/mcrypt.ciphers.php for available ciphers
	 */
	'mcrypt_cipher' => 'rijndael-256',

	/**
	 * MCRYPT cipher mode to use
	 *
	 * See http://www.php.net/manual/en/mcrypt.constants.php for available cipher modes
	 */
	'mcrypt_mode'	=> 'cbc'

);

/* End of file config/crypt.php */
