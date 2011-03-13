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
 * Format class
 *
 * Help convert between various formats such as XML, JSON, CSV, etc.
 *
 * @package		Fuel
 * @category	Core
 * @author		Phil Sturgeon - Fuel Development Team
 * @copyright	(c) 2008-2010 Kohana Team
 * @copyright	2010 - 2011 Fuel Development Team
 * @link		http://fuelphp.com/docs/classes/format.html
 */
class Format {

	// Array to convert
	protected $_data = array();

	// View filename
	protected $_from_type = null;

	/**
	 * Returns an instance of the Format object.
	 *
	 *     echo Format::factory(array('foo' => 'bar'))->to_xml();
	 *
	 * @param   mixed  general date to be converted
	 * @param   string  data format the file was provided in
	 * @return  Factory
	 */
	public static function factory($data = null, $from_type = null)
	{
		return new static($data, $from_type);
	}

	/**
	 * Do not use this directly, call factory()
	 */
	public function __construct($data = null, $from_type = null)
	{
		// If the provided data is already formatted we should probably convert it to an array
		if ($from_type !== null)
		{
			if (method_exists($this, '_from_' . $from_type))
			{
				$data = call_user_func(array($this, '_from_' . $from_type), $data);
			}

			else
			{
				throw new Fuel_Exception('Format class does not support conversion from "' . $from_type . '".');
			}
		}

		$this->_data = $data;
	}

	// FORMATING OUTPUT ---------------------------------------------------------

	public function to_array($data = null)
	{
		if ($data === null)
		{
			$data = $this->_data;
		}

		$array = array();

		foreach ((array) $this->_data as $key => $value)
		{
			if (is_object($value) or is_array($value))
			{
				$array[$key] = static::to_array($value);
			}

			else
			{
				$array[$key] = $value;
			}
		}

		return $array;
	}

	// Format XML for output
	public function to_xml($data = null, $structure = NULL, $basenode = 'xml')
	{
		if ($data == null)
		{
			$data = $this->_data;
		}
		
		// turn off compatibility mode as simple xml throws a wobbly if you don't.
		if (ini_get('zend.ze1_compatibility_mode') == 1)
		{
			ini_set('zend.ze1_compatibility_mode', 0);
		}

		if ($structure == NULL)
		{
			$structure = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$basenode />");
		}

		// Force it to be something useful
		if ( ! is_array($data) AND ! is_object($data))
		{
			$data = (array) $data;
		}
		
		foreach ($data as $key => $value)
		{
			// no numeric keys in our xml please!
			if (is_numeric($key))
			{
				// make string key...
				//$key = "item_". (string) $key;
				$key = "item";
			}

			// replace anything not alpha numeric
			$key = preg_replace('/[^a-z_\-0-9]/i', '', $key);

			// if there is another array found recrusively call this function
			if (is_array($value) OR is_object($value))
			{
				$node = $structure->addChild($key);
				// recrusive call.
				$this->to_xml($value, $node, $basenode);
			}
			else
			{
				// Actual boolean values need to be converted to numbers
				is_bool($value) AND $value = (int) $value;

				// add single node.
				$value = htmlspecialchars(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, "UTF-8");

				$structure->addChild($key, $value);
			}
		}

		// pass back as string. or simple xml object if you want!
		return $structure->asXML();
	}

	// Format HTML for output
//	private function to_html($data = array())
//	{
//		// Multi-dimentional array
//		if (isset($data[0]))
//		{
//			$headings = array_keys($data[0]);
//		}
//
//		// Single array
//		else
//		{
//			$headings = array_keys($data);
//			$data = array($data);
//		}
//
//		$this->load->library('table');
//
//		$this->table->set_heading($headings);
//
//		foreach ($data as &$row)
//		{
//			$this->table->add_row($row);
//		}
//
//		return $this->table->generate();
//	}

	// Format HTML for output
	public function to_csv()
	{
		$data = $this->_data;
		
		// Multi-dimentional array
		if (is_array($data) and isset($data[0]))
		{
			$headings = array_keys($data[0]);
		}

		// Single array
		else
		{
			$headings = array_keys((array) $data);
			$data = array($data);
		}

		$output = implode(',', $headings) . "\r\n";
		foreach ($data as &$row)
		{
			$output .= '"' . implode('","', (array) $row) . "\"\r\n";
		}

		return $output;
	}

	// Encode as JSON
	public function to_json()
	{
		return json_encode($this->_data);
	}

	// Encode as Serialized array
	public function to_serialized()
	{
		return serialize($this->_data);
	}


	// Format XML for output
	protected function _from_xml($string)
	{
		return (array) simplexml_load_string($string);
	}

	// Format HTML for output
	// This function is DODGY! Not perfect CSV support but works with my REST_Controller
	protected function _from_csv($string)
	{
		$data = array();

		// Splits
		$rows = explode("\n", trim($string));
		$headings = explode(',', array_shift($rows));
		foreach ($rows as $row)
		{
			// The substr removes " from start and end
			$data_fields = explode('","', trim(substr($row, 1, -1)));

			if (count($data_fields) == count($headings))
			{
				$data[] = array_combine($headings, $data_fields);
			}
		}

		return $data;
	}

	// Encode as JSON
	private function _from_json($string)
	{
		return json_decode(trim($string));
	}

	// Encode as Serialized array
	private function _from_serialize($string)
	{
		return unserialize(trim($string));
	}
	
}

/* End of file view.php */