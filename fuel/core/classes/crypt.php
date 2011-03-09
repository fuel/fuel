<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2011 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Core;

/**
 * Crypt Class
 *
 * @package		Fuel
 * @category	Core
 * @author		Harro "WanWizard" Verton
 * @link		http://fuelphp.com/docs/classes/crypt.html
 */
class Crypt {

	/**
	 * @var	boolean	idicator for the usage of mcrypt
	 */
	public static $use_mcrypt = true;

	/**
	 * @var	string	Magic salt to use as encryption key
	 */
	public static $salt = 'sup3rs3Cr3tk3y564';

	/**
	 * @var	boolean	idicator for the availability of mcrypt
	 */
	protected static $have_mcrypt = false;

	/**
	 * @var	int	default mcrypt cipher to use
	 */
	protected static $mcrypt_cipher = 'rijndael-256';

	/**
	 * @var	int	default mcrypt mode  to use
	 */
	protected static $mcrypt_mode = 'cbc';

	// --------------------------------------------------------------------

	/*
	 * initialisation and auto configuration
	 */
	public static function _init()
	{
		// check we we have the mcrypt library available
		static::$have_mcrypt = function_exists('mcrypt_encrypt');

		// load the config
		\Config::load('crypt', true);

		$config = \Config::get('crypt', array ());

		// update the defaults with the configed values
		foreach($config as $key => $value)
		{
			isset(static::${$key}) && static::${$key} = $value;
		}
	}

	// --------------------------------------------------------------------

	/*
	 * set a configuration value
	 *
	 * @param	string	name of the configuration key
	 * @param	string	value to be set
	 * @access	public
	 * @return	void
	 */
	public static function set($name = false, $value = null)
	{
		$name && isset(static::${$name}) && static::${$name} = $value;
	}

	// --------------------------------------------------------------------

	/*
	 * get a configuration value
	 *
	 * @param	string	name of the configuration key
	 * @access	public
	 * @return	string	the configuration key value, or false if the key is invalid
	 */
	public static function get($name = false)
	{
		return $name && isset(static::${$name}) ? static::${$name} : false;
	}

	// --------------------------------------------------------------------

	/*
	 * encrypt a string value, optionally with a custom salt
	 *
	 * @param	string	value to encrypt
	 * @param	string	optional salt to be used for this encryption
	 * @access	public
	 * @return	string	encrypted value
	 */
	public static function encode($value, $salt = false)
	{
		// if no salt is given, use the default salt
		if ($salt === false)
		{
			$salt = static::$salt;
		}

		// check if we have mcrypt available, and we want to use it
		if (static::$have_mcrypt && static::$use_mcrypt)
		{
			// encrypt using mcrypt
			$iv_size = mcrypt_get_iv_size(static::$mcrypt_cipher, static::$mcrypt_mode);
			$iv_vector = mcrypt_create_iv($iv_size, MCRYPT_RAND);
			$value = '1:'.static::_add_cipher_noise($iv_vector.mcrypt_encrypt(static::$mcrypt_cipher, $salt, $value, static::$mcrypt_mode, $iv_vector), $salt);
		}
		else
		{
			$keys = static::_crypt_key($salt);
			for($i = 0; $i < strlen($value); $i++){
				$id = $i % (count($keys)-3);
				$ord = ord($value{$i});
				$ord = $ord OR ord($keys[$id]);
				$id++;
				$ord = $ord AND ord($keys[$id]);
				$id++;
				$ord = $ord XOR ord($keys[$id]);
				$id++;
				$ord = $ord + ord($keys[$id]);
				$value{$i} = chr($ord);
			}
			$value = '0:'.$value;
		}

		// make the encoding URL save
		return strtr(
				base64_encode($value),
				array(
					'+' => '.',
					'=' => '-',
					'/' => '~'
				)
			);
	}

	// --------------------------------------------------------------------

	/*
	 * decrypt a string value, optionally with a custom salt
	 *
	 * the method automatically detects if mcrypt was used on encryption
	 *
	 * @param	string	value to decrypt
	 * @param	string	optional salt to be used for this encryption
	 * @access	public
	 * @return	string	encrypted value
	 */
	public static function decode($value, $salt = false)
	{
		// if no salt is given, use the default salt
		if ($salt === false)
		{
			$salt = static::$salt;
		}

		// decode the value passed
		$value = base64_decode(strtr(
				$value,
				array(
					'.' => '+',
					'-' => '=',
					'~' => '/'
				)
			));

		// check if we have mcrypt available, and the value was encrypted by mcrypt
		if (static::$have_mcrypt && substr($value,0,2) == '1:')
		{
			// decrypt using mcrypt
			$value = static::_remove_cipher_noise(substr($value,2), $salt);
			$iv_size = mcrypt_get_iv_size(static::$mcrypt_cipher, static::$mcrypt_mode);

			if ($iv_size > strlen($value))
			{
				return false;
			}

			$iv_vector = substr($value, 0, $iv_size);
			$value = substr($value, $iv_size);
			$value = rtrim(mcrypt_decrypt(static::$mcrypt_cipher, $salt, $value, static::$mcrypt_mode, $iv_vector), "\0");
		}
		else
		{
			// was the value encrypted using mcrypt
			if (substr($value,0,2) == '1:')
			{
				// houston, we have a problem!
				throw new \Fuel_Exception('Encrypted string was encrypted using the PHP mcrypt library, which is not loaded on this system.');
			}

			$value = substr($value,2);
			$keys = static::_crypt_key($salt);
			for($i = 0; $i < strlen($value); $i++){
				$id = $i % (count($keys)-3);
				$ord = ord($value{$i});
				$ord = $ord XOR ord($keys[$id]);
				$id++;
				$ord = $ord AND ord($keys[$id]);
				$id++;
				$ord = $ord OR ord($keys[$id]);
				$id++;
				$ord = $ord - ord($keys[$id]);
				$value{$i} = chr($ord);
			}
		}

		return $value;
	}

	// --------------------------------------------------------------------

	/**
	 * Adds permuted noise to the IV + encrypted data to protect
	 * against Man-in-the-middle attacks on CBC mode ciphers
	 * http://www.ciphersbyritter.com/GLOSSARY.HTM#IV
	 *
	 * @param	string
	 * @param	string
	 * @access	private
	 * @return	string
	 */
	protected static function _add_cipher_noise($value, $salt)
	{
		$keyhash = sha1($salt);
		$keylen = strlen($keyhash);
		$str = '';

		for ($i = 0, $j = 0, $len = strlen($value); $i < $len; ++$i, ++$j)
		{
			if ($j >= $keylen)
			{
				$j = 0;
			}

			$str .= chr((ord($value[$i]) + ord($keyhash[$j])) % 256);
		}

		return $str;
	}

	// --------------------------------------------------------------------

	/**
	 * Removes permuted noise from the IV + encrypted data, reversing
	 * _add_cipher_noise()
	 *
	 * @param	string
	 * @param	string
	 * @access	private
	 * @return	string
	 */
	protected static function _remove_cipher_noise($value, $salt)
	{
		$keyhash = sha1($salt);
		$keylen = strlen($keyhash);
		$str = '';

		for ($i = 0, $j = 0, $len = strlen($value); $i < $len; ++$i, ++$j)
		{
			if ($j >= $keylen)
			{
				$j = 0;
			}

			$temp = ord($value[$i]) - ord($keyhash[$j]);

			if ($temp < 0)
			{
				$temp = $temp + 256;
			}

			$str .= chr($temp);
		}

		return $str;
	}

	// --------------------------------------------------------------------

	/**
	 * generate a crypt key for non-mcrypt encryption
	 *
	 * @param	string	salt
	 * @access	private
	 * @return	array	keys used for the encryption algorithm
	 */
	protected static function _crypt_key($salt)
	{
		$keys = array();
		$c_key = base64_encode(sha1(md5($salt)));
		$c_key = substr($c_key, 0, round(ord($salt{0})/5));
		$c2_key = base64_encode(md5(sha1($salt)));
		$last = strlen($salt) - 1;
		$c2_key = substr($c2_key, 1, round(ord($salt{$last})/7));
		$c3_key = base64_encode(sha1(md5($c_key).md5($c2_key)));
		$mid = round($last/2);
		$c3_key = substr($c3_key, 1, round(ord($salt{$mid})/9));
		$c_key = $c_key.$c2_key.$c3_key;
		$c_key = base64_encode($c_key);
		for($i = 0; $i < strlen($c_key); $i++){
			$keys[] = $c_key[$i];
		}
		return $keys;
	}

}

/* End of file encrypt.php */
