<?php
/**
 * Inspekt Cage - main source file
 *
 * @author Chris Shiflett <chris@shiflett.org>
 * @author Ed Finkler <coj@funkatron.com>
 *
 * @package Inspekt
 */

namespace Fuel;

define('ISPK_ARRAY_PATH_SEPARATOR', '/');

define('ISPK_RECURSION_MAX', 15);

/**
 * @package Inspekt
 */
class Inspekt_Cage implements \IteratorAggregate, \ArrayAccess, \Countable {

	/**
	 * {@internal The raw source data.  Although tempting, NEVER EVER
	 * EVER access the data directly using this property!}}
	 *
	 * Don't try to access this.  ever.  Now that we're safely on PHP5, we'll
	 * enforce this with the "protected" keyword.
	 *
	 * @var array
	 */
	protected $_source = NULL;
	/**
	 * where we store user-defined methods
	 *
	 * @var array
	 */
	public $_user_accessors = array();
	/**
	 * the holding property for autofilter config
	 *
	 * @var array
	 */
	public $_autofilter_conf = NULL;
	/**
	 *
	 * @var HTMLPurifer
	 */
	public $purifier = NULL;

	/**
	 *
	 * @return Inspekt_Cage
	 */
	public function Inspekt_Cage()
	{
		// placeholder -- we're using a factory here
	}

	/**
	 * Takes an array and wraps it inside an object.  If $strict is not set to
	 * FALSE, the original array will be destroyed, and the data can only be
	 * accessed via the object's accessor methods
	 *
	 * @param array $source
	 * @param string $conf_file
	 * @param string $conf_section
	 * @param boolean $strict
	 * @return Inspekt_Cage
	 *
	 * @static
	 */
	static public function factory(&$source, $config = array(), $strict = true, $maintain_original = false)
	{

		if (!is_array($source))
		{
			Inspekt_Error::raise_error('$source ' . $source . ' is not an array', E_USER_WARNING);
		}

		$cage = new Inspekt_Cage();
		$cage->_set_source($source);
		$cage->_parse_and_apply_auto_filters($config);

		if ($strict)
		{
			$source = NULL;
		}
		else
		{
			if ($maintain_original)
			{
				$source = Inspekt::convert_array_object_to_array($cage->_source);
			}
		}

		return $cage;
	}

	/**
	 * {@internal we use this to set the data array in factory()}}
	 *
	 * @see factory()
	 * @param array $newsource
	 */
	private function _set_source(&$newsource)
	{
		$this->_source = Inspekt::convert_array_to_array_object($newsource);
	}

	/**
	 * Returns an iterator for looping through an ArrayObject.
	 *
	 * @access public
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		return $this->_source->getIterator();
	}

	/**
	 * Sets the value at the specified $offset to value$
	 * in $this->_source.
	 *
	 * @param mixed $offset
	 * @param mixed $value
	 * @access public
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
		$this->_source->offsetSet($offset, $value);
	}

	/**
	 * Returns whether the $offset exists in $this->_source.
	 *
	 * @param mixed $offset
	 * @access public
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		return $this->_source->offsetExists($offset);
	}

	/**
	 * Unsets the value in $this->_source at $offset.
	 *
	 * @param mixed $offset
	 * @access public
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		$this->_source->offsetUnset($offset);
	}

	/**
	 * Returns the value at $offset from $this->_source.
	 *
	 * @param mixed $offset
	 * @access public
	 * @return void
	 */
	public function offsetGet($offset)
	{
		return $this->_source->offsetGet($offset);
	}

	/**
	 * Returns the number of elements in $this->_source.
	 *
	 * @access public
	 * @return int
	 */
	public function count()
	{
		return $this->_source->count();
	}

	/**
	 * Load the HTMLPurifier library and instantiate the object
	 * @param string $path the full path to the HTMLPurifier.auto.php base file. Optional if HTMLPurifier is already in your include_path
	 */
	public function load_html_purifier($path=null, $opts=null)
	{
		if (isset($path))
		{
			include_once($path);
		}
		else
		{
			include_once('HTMLPurifier.auto.php');
		}

		if (isset($opts) && is_array($opts))
		{
			$config = $this->_build_html_purifier_config($opts);
		}
		else
		{
			$config = null;
		}

		$this->purifier = new HTMLPurifier($config);
	}

	/**
	 *
	 * @param HTMLPurifer $pobj an HTMLPurifer Object
	 */
	public function set_html_purifier($pobj)
	{
		$this->purifier = $pobj;
	}

	/**
	 * @return HTMLPurifier
	 */
	public function get_html_purifier()
	{
		return $this->purifier;
	}

	protected function _build_html_purifier_config($opts)
	{
		$config = HTMLPurifier_Config::createDefault();
		foreach ($opts as $key => $val)
		{
			$config->set($key, $val);
		}
		return $config;
	}

	protected function _parse_and_apply_auto_filters($config)
	{
		$this->_autofilter_conf = $config;

		$this->_apply_auto_filters();
	}

	protected function _apply_auto_filters()
	{

		if (isset($this->_autofilter_conf) && is_array($this->_autofilter_conf))
		{

			foreach ($this->_autofilter_conf as $key => $filters)
			{

				// get universal filter key
				if ($key == '*')
				{

					// get filters for this key
					$uni_filters = explode(',', $this->_autofilter_conf[$key]);
					array_walk($uni_filters, 'trim');

					// apply uni filters
					foreach ($uni_filters as $this_filter)
					{
						foreach ($this->_source as $key => $val)
						{
							$this->_source[$key] = $this->$this_filter($key);
						}
					}
					//echo "<pre>UNI FILTERS"; echo var_dump($this->_source); echo "</pre>\n";
				}
				elseif ($val == $this->keyExists($key))
				{

					// get filters for this key
					$filters = explode(',', $this->_autofilter_conf[$key]);
					array_walk($filters, 'trim');

					// apply filters
					foreach ($filters as $this_filter)
					{
						$this->_set_value($key, $this->$this_filter($key));
					}
					//echo "<pre> Filter $this_filter/$key: "; echo var_dump($this->_source); echo "</pre>\n";
				}
			}
		}
	}

	public function __call($name, $args)
	{
		if (in_array($name, $this->_user_accessors))
		{

			$acc = new $name($this, $args);
			/*
			  this first argument should always be the key we're accessing
			 */
			return $acc->run($args[0]);
		}
		else
		{
			Inspekt_Error::raise_error("The accessor $name does not exist and is not registered", E_USER_ERROR);
			return false;
		}
	}

	/**
	 * This method lets the developer add new accessor methods to a cage object
	 * Note that calling these will be quite a bit slower, because we have to
	 * use call_user_func()
	 *
	 * The dev needs to define a procedural function like so:
	 *
	 * <code>
	 * function foo_bar($cage_object, $arg2, $arg3, $arg4, $arg5...) {
	 *    ...
	 * }
	 * </code>
	 *
	 * @param string $method_name
	 * @return void
	 * @author Ed Finkler
	 */
	public function add_accessor($accessor_name)
	{
		$this->_user_accessors[] = $accessor_name;
	}

	/**
	 * Returns only the alphabetic characters in value.
	 *
	 * @param mixed $key
	 * @return mixed
	 *
	 * @tag filter
	 */
	public function get_alpha($key)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		return Inspekt::get_alpha($this->_get_value($key));
	}

	/**
	 * Returns only the alphabetic characters and digits in value.
	 *
	 * @param mixed $key
	 * @return mixed
	 *
	 * @tag filter
	 */
	public function get_alnum($key)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		return Inspekt::get_alnum($this->_get_value($key));
	}

	/**
	 * Returns only the digits in value. This differs from get_int().
	 *
	 * @param mixed $key
	 * @return mixed
	 *
	 * @tag filter
	 */
	public function get_digits($key)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		return Inspekt::get_digits($this->_get_value($key));
	}

	/**
	 * Returns dirname(value).
	 *
	 * @param mixed $key
	 * @return mixed
	 *
	 * @tag filter
	 */
	public function get_dir($key)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		return Inspekt::get_dir($this->_get_value($key));
	}

	/**
	 * Returns (int) value.
	 *
	 * @param mixed $key
	 * @return int
	 *
	 * @tag filter
	 */
	public function get_int($key)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		return Inspekt::get_int($this->_get_value($key));
	}

	/**
	 * Returns realpath(value).
	 *
	 * @param mixed $key
	 * @return mixed
	 *
	 * @tag filter
	 */
	public function get_path($key)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		return Inspekt::get_path($this->_get_value($key));
	}

	/**
	 * Returns ROT13-encoded version
	 *
	 * @param string $key
	 * @return mixed
	 * @tag hash
	 */
	public function get_rot13($key)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		return Inspekt::get_rot13($this->_get_value($key));
	}

	/**
	 * This returns the value of the given key passed through the HTMLPurifer
	 * object, if it is instantiated with Inspekt_Cage::loadHTMLPurifer
	 *
	 * @param string $key
	 * @return mixed purified HTML version of input
	 * @tag filter
	 */
	public function get_purified_html($key)
	{
		if (!isset($this->purifier))
		{
			Inspekt_Error::raise_error("HTMLPurifier was not loaded", E_USER_WARNING);
			return false;
		}

		if (!$this->keyExists($key))
		{
			return false;
		}
		$val = $this->_get_value($key);
		if (Inspekt::is_array_or_array_object($val))
		{
			return $this->purifier->purifyArray($val);
		}
		else
		{
			return $this->purifier->purify($val);
		}
	}

	/**
	 * Returns value.
	 *
	 * @param string $key
	 * @return mixed
	 *
	 * @tag filter
	 */
	public function get_raw($key)
	{
		if (!$this->keyExists($key))
		{
			return null;
		}
		return $this->_get_value($key);
	}

	/**
	 * Returns value if every character is alphabetic or a digit,
	 * FALSE otherwise.
	 *
	 * @param mixed $key
	 * @return mixed
	 *
	 * @tag validator
	 */
	public function test_alnum($key)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		if (Inspekt::is_alnum($this->_get_value($key)))
		{
			return $this->_get_value($key);
		}

		return FALSE;
	}

	/**
	 * Returns value if every character is alphabetic, FALSE
	 * otherwise.
	 *
	 * @param mixed $key
	 * @return mixed
	 *
	 * @tag validator
	 */
	public function test_alpha($key)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		if (Inspekt::is_alpha($this->_get_value($key)))
		{
			return $this->_get_value($key);
		}

		return FALSE;
	}

	/**
	 * Returns value if it is greater than or equal to $min and less
	 * than or equal to $max, FALSE otherwise. If $inc is set to
	 * FALSE, then the value must be strictly greater than $min and
	 * strictly less than $max.
	 *
	 * @param mixed $key
	 * @param mixed $min
	 * @param mixed $max
	 * @param boolean $inc
	 * @return mixed
	 *
	 * @tag validator
	 */
	public function test_between($key, $min, $max, $inc = TRUE)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		if (Inspekt::is_between($this->_get_value($key), $min, $max, $inc))
		{
			return $this->_get_value($key);
		}

		return FALSE;
	}

	/**
	 * Returns value if it is a valid credit card number format. The
	 * optional second argument allows developers to indicate the
	 * type.
	 *
	 * @param mixed $key
	 * @param mixed $type
	 * @return mixed
	 *
	 * @tag validator
	 */
	public function test_ccnum($key, $type = NULL)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		if (Inspekt::is_ccnum($this->_get_value($key), $type))
		{
			return $this->_get_value($key);
		}

		return FALSE;
	}

	/**
	 * Returns $value if it is a valid date, FALSE otherwise. The
	 * date is required to be in ISO 8601 format.
	 *
	 * @param mixed $key
	 * @return mixed
	 *
	 * @tag validator
	 */
	public function test_date($key)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		if (Inspekt::is_date($this->_get_value($key)))
		{
			return $this->_get_value($key);
		}

		return FALSE;
	}

	/**
	 * Returns value if every character is a digit, FALSE otherwise.
	 * This is just like is_int(), except there is no upper limit.
	 *
	 * @param mixed $key
	 * @return mixed
	 *
	 * @tag validator
	 */
	public function test_digits($key)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		if (Inspekt::is_digits($this->_get_value($key)))
		{
			return $this->_get_value($key);
		}

		return FALSE;
	}

	/**
	 * Returns value if it is a valid email format, FALSE otherwise.
	 *
	 * @param mixed $key
	 * @return mixed
	 *
	 * @tag validator
	 */
	public function test_email($key)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		if (Inspekt::is_email($this->_get_value($key)))
		{
			return $this->_get_value($key);
		}

		return FALSE;
	}

	/**
	 * Returns value if it is a valid float value, FALSE otherwise.
	 *
	 * @param mixed $key
	 * @return mixed
	 *
	 * @tag validator
	 */
	public function test_float($key)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		if (Inspekt::is_float($this->_get_value($key)))
		{
			return $this->_get_value($key);
		}

		return FALSE;
	}

	/**
	 * Returns value if it is greater than $min, FALSE otherwise.
	 *
	 * @param mixed $key
	 * @param mixed $min
	 * @return mixed
	 *
	 * @tag validator
	 */
	public function test_greater_than($key, $min = NULL)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		if (Inspekt::is_greater_than($this->_get_value($key), $min))
		{
			return $this->_get_value($key);
		}

		return FALSE;
	}

	/**
	 * Returns value if it is a valid hexadecimal format, FALSE
	 * otherwise.
	 *
	 * @param mixed $key
	 * @return mixed
	 *
	 * @tag validator
	 */
	public function test_hex($key)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		if (Inspekt::is_hex($this->_get_value($key)))
		{
			return $this->_get_value($key);
		}

		return FALSE;
	}

	/**
	 * Returns value if it is a valid hostname, FALSE otherwise.
	 * Depending upon the value of $allow, Internet domain names, IP
	 * addresses, and/or local network names are considered valid.
	 * The default is HOST_ALLOW_ALL, which considers all of the
	 * above to be valid.
	 *
	 * @param mixed $key
	 * @param integer $allow bitfield for HOST_ALLOW_DNS, HOST_ALLOW_IP, HOST_ALLOW_LOCAL
	 * @return mixed
	 *
	 * @tag validator
	 */
	public function test_hostname($key, $allow = ISPK_HOST_ALLOW_ALL)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		if (Inspekt::is_hostname($this->_get_value($key), $allow))
		{
			return $this->_get_value($key);
		}

		return FALSE;
	}

	/**
	 * Returns value if it is a valid integer value, FALSE otherwise.
	 *
	 * @param mixed $key
	 * @return mixed
	 *
	 * @tag validator
	 */
	public function test_int($key)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		if (Inspekt::is_int($this->_get_value($key)))
		{
			return $this->_get_value($key);
		}

		return FALSE;
	}

	/**
	 * Returns value if it is a valid IP format, FALSE otherwise.
	 *
	 * @param mixed $key
	 * @return mixed
	 *
	 * @tag validator
	 */
	public function test_ip($key)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		if (Inspekt::is_ip($this->_get_value($key)))
		{
			return $this->_get_value($key);
		}

		return FALSE;
	}

	/**
	 * Returns value if it is less than $max, FALSE otherwise.
	 *
	 * @param mixed $key
	 * @param mixed $max
	 * @return mixed
	 *
	 * @tag validator
	 */
	public function test_less_than($key, $max = NULL)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		if (Inspekt::is_less_than($this->_get_value($key), $max))
		{
			return $this->_get_value($key);
		}

		return FALSE;
	}

	/**
	 * Returns value if it is one of $allowed, FALSE otherwise.
	 *
	 * @param mixed $key
	 * @return mixed
	 *
	 * @tag validator
	 */
	public function test_one_of($key, $allowed = NULL)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		if (Inspekt::is_one_of($this->_get_value($key), $allowed))
		{
			return $this->_get_value($key);
		}

		return FALSE;
	}

	/**
	 * Returns value if it is a valid phone number format, FALSE
	 * otherwise. The optional second argument indicates the country.
	 *
	 * @param mixed $key
	 * @return mixed
	 *
	 * @tag validator
	 */
	public function test_phone($key, $country = 'US')
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		if (Inspekt::is_phone($this->_get_value($key), $country))
		{
			return $this->_get_value($key);
		}

		return FALSE;
	}

	/**
	 * Returns value if it matches $pattern, FALSE otherwise. Uses
	 * preg_match() for the matching.
	 *
	 * @param mixed $key
	 * @param mixed $pattern
	 * @return mixed
	 *
	 * @tag validator
	 */
	public function test_regex($key, $pattern = NULL)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		if (Inspekt::is_regex($this->_get_value($key), $pattern))
		{
			return $this->_get_value($key);
		}

		return FALSE;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $key
	 * @return unknown
	 *
	 * @tag validator
	 */
	public function test_uri($key)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		if (Inspekt::is_uri($this->_get_value($key)))
		{
			return $this->_get_value($key);
		}

		return FALSE;
	}

	/**
	 * Returns value if it is a valid US ZIP, FALSE otherwise.
	 *
	 * @param mixed $key
	 * @return mixed
	 *
	 * @tag validator
	 */
	public function test_zip($key)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		if (Inspekt::is_zip($this->_get_value($key)))
		{
			return $this->_get_value($key);
		}

		return FALSE;
	}

	/**
	 * Returns value with all tags removed.
	 *
	 * @param mixed $key
	 * @return mixed
	 *
	 * @tag filter
	 */
	public function no_tags($key)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		return Inspekt::no_tags($this->_get_value($key));
	}

	/**
	 * Returns basename(value).
	 *
	 * @param mixed $key
	 * @return mixed
	 *
	 * @tag filter
	 */
	public function no_path($key)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		return Inspekt::no_path($this->_get_value($key));
	}

	public function no_tags_or_special($key)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		return Inspekt::no_tags_or_special($this->_get_value($key));
	}

	public function esc_mysql($key, $conn=null)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		if (isset($conn))
		{
			return Inspekt::esc_mysql($this->_get_value($key), $conn);
		}
		else
		{
			return Inspekt::esc_mysql($this->_get_value($key));
		}
	}

	public function esc_pgsql($key, $conn=null)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		if (isset($conn))
		{
			return Inspekt::esc_pgsql($this->_get_value($key), $conn);
		}
		else
		{
			return Inspekt::esc_pgsql($this->_get_value($key));
		}
	}

	public function esc_pgsql_bytea($key, $conn=null)
	{
		if (!$this->keyExists($key))
		{
			return false;
		}
		if (isset($conn))
		{
			return Inspekt::esc_pgsql_bytea($this->_get_value($key), $conn);
		}
		else
		{
			return Inspekt::esc_pgsql_bytea($this->_get_value($key));
		}
	}

	/**
	 * Checks if a key exists
	 *
	 * @param mixed $key
	 * @param boolean $return_value  whether or not to return the value if key exists. defaults to FALSE.
	 * @return mixed
	 *
	 */
	public function keyExists($key, $return_value=false)
	{
		if (strpos($key, ISPK_ARRAY_PATH_SEPARATOR) !== FALSE)
		{
			$key = trim($key, ISPK_ARRAY_PATH_SEPARATOR);
			$keys = explode(ISPK_ARRAY_PATH_SEPARATOR, $key);
			return $this->_key_exists_recursive($keys, $this->_source);
		}
		else
		{
			if ($exists = array_key_exists($key, $this->_source))
			{
				if ($return_value)
				{
					return $this->_source[$key];
				}
				else
				{
					return $exists;
				}
			}
			else
			{
				return FALSE;
			}
		}
	}

	protected function _key_exists_recursive($keys, $data_array)
	{
		$thiskey = current($keys);

		if (is_numeric($thiskey))
		{ // force numeric strings to be integers
			$thiskey = (int) $thiskey;
		}

		if (array_key_exists($thiskey, $data_array))
		{
			if (sizeof($keys) == 1)
			{
				return true;
			}
			elseif ($data_array[$thiskey] instanceof \ArrayObject)
			{
				unset($keys[key($keys)]);
				return $this->_key_exists_recursive($keys, $data_array[$thiskey]);
			}
		}
		else
		{ // if any key DNE, return false
			return false;
		}
	}

	/**
	 * Retrieves a value from the _source array. This should NOT be called directly, but needs to be public
	 * for use by AccessorAbstract. Maybe a different approach should be considered
	 *
	 * @param string $key
	 * @return mixed
	 * @private
	 */
	public function _get_value($key)
	{
		if (strpos($key, ISPK_ARRAY_PATH_SEPARATOR) !== FALSE)
		{
			$key = trim($key, ISPK_ARRAY_PATH_SEPARATOR);
			$keys = explode(ISPK_ARRAY_PATH_SEPARATOR, $key);
			return $this->_get_value_recursive($keys, $this->_source);
		}
		else
		{
			return $this->_source[$key];
		}
	}

	protected function _get_value_recursive($keys, $data_array, $level=0)
	{
		$thiskey = current($keys);

		if (is_numeric($thiskey))
		{ // force numeric strings to be integers
			$thiskey = (int) $thiskey;
		}

		if (array_key_exists($thiskey, $data_array))
		{
			if (sizeof($keys) == 1)
			{
				return $data_array[$thiskey];
			}
			elseif ($data_array[$thiskey] instanceof \ArrayObject)
			{
				if ($level < ISPK_RECURSION_MAX)
				{
					unset($keys[key($keys)]);
					return $this->_get_value_recursive($keys, $data_array[$thiskey], $level + 1);
				}
				else
				{
					Inspekt_Error::raise_error('Inspekt recursion limit met', E_USER_WARNING);
					return false;
				}
			}
		}
		else
		{ // if any key DNE, return false
			return false;
		}
	}

	/**
	 * Sets a value in the _source array
	 *
	 * @param mixed $key
	 * @param mixed $val
	 * @return mixed
	 */
	protected function _set_value($key, $val)
	{
		if (strpos($key, ISPK_ARRAY_PATH_SEPARATOR) !== FALSE)
		{
			$key = trim($key, ISPK_ARRAY_PATH_SEPARATOR);
			$keys = explode(ISPK_ARRAY_PATH_SEPARATOR, $key);
			return $this->_set_value_recursive($keys, $this->_source);
		}
		else
		{
			$this->_source[$key] = $val;
			return $this->_source[$key];
		}
	}

	protected function _set_value_recursive($keys, $val, $data_array, $level=0)
	{
		$thiskey = current($keys);

		if (is_numeric($thiskey))
		{ // force numeric strings to be integers
			$thiskey = (int) $thiskey;
		}

		if (array_key_exists($thiskey, $data_array))
		{
			if (sizeof($keys) == 1)
			{
				$data_array[$thiskey] = $val;
				return $data_array[$thiskey];
			}
			elseif ($data_array[$thiskey] instanceof \ArrayObject)
			{
				if ($level < ISPK_RECURSION_MAX)
				{
					unset($keys[key($keys)]);
					return $this->_set_value_recursive($keys, $val, $data_array[$thiskey], $level + 1);
				}
				else
				{
					Inspekt_Error::raise_error('Inspekt recursion limit met', E_USER_WARNING);
					return false;
				}
			}
		}
		else
		{ // if any key DNE, return false
			return false;
		}
	}

}
