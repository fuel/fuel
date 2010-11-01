<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Fuel Development Team
 * @license		MIT License
 * @copyright	2010 Dan Horrigan
 * @link		http://fuelphp.com
 */

class Fuel_Cache_Storage_File extends Cache_Storage_Driver {

	protected $path = '';

	const PROPS_TAG = 'Fuel_Cache_Properties';
	
	public function __construct($identifier, $config)
	{
		parent::__construct($identifier, $config);
		
		$this->path = Config::get('cache.path') != FALSE ? Config::get('cache.path') : APPPATH.'cache'.DIRECTORY_SEPARATOR;
		if ( ! is_dir($this->path) || ! is_writable($this->path))
		{
			throw new Cache_Exception('Cache directory does not exist or is not writable.');
		}
	}
	
	protected function identifier_to_path( $identifier )
	{
		// cleanup to only allow alphanum chars, dashes, dots & underscores
		if (preg_match('/^([a-z0-9_\.\-]*)$/i', $identifier) === 0)
		{
			throw new Cache_Exception('Cache identifier can only contain alphanumeric characters, underscores, dashes & dots.');
		}
		
		// replace dots with dashes
		$identifier = str_replace('.', DIRECTORY_SEPARATOR, $identifier);
		
		return $identifier;
	}

	protected function _set()
	{
		$payload = $this->prep_contents();
		$id_path = $this->identifier_to_path( $this->identifier );

		// create directory if necessary
		$subdirs = explode(DIRECTORY_SEPARATOR, $id_path);
		if (count($subdirs) > 1)
		{
			array_pop($subdirs);
			$test_path = $this->path.implode(DIRECTORY_SEPARATOR, $subdirs);

			// check if specified subdir exists
			if ( ! @is_dir($test_path))
			{
				// create non existing dir
				if ( ! @mkdir($test_path, 0755, true)) return false;
			}
		}
		
		// write the cache
		$file = $this->path.$id_path.'.cache';
		$handle = fopen($file, 'c');
		if ($handle)
		{
			// wait for a lock
			while( ! flock($handle, LOCK_EX));

			// write the session data
			fwrite($handle, $payload);

			//release the lock
			flock($handle, LOCK_UN);

			// close the file
			fclose($handle);
		}
	}

	protected function _get()
	{
		$id_path = $this->identifier_to_path( $this->identifier );
		$file = $this->path.$id_path.'.cache';
		if ( ! file_exists($file))
			return false;

		$handle = fopen($file, 'r');
		if ( ! $handle)
			return false;

		// wait for a lock
		while( ! flock($handle, LOCK_EX));

		// read the session data
		$payload = fread($handle, filesize($file));

		//release the lock
		flock($handle, LOCK_UN);

		// close the file
		fclose($handle);

		try
		{
			$this->unprep_contents($payload);
		}
		catch(Cache_Exception $e)
		{
			return false;
		}

		return true;
	}

	protected function prep_contents()
	{
		$properties = array(
			'created'			=> $this->created,
			'expiration'		=> $this->expiration,
			'dependencies'		=> $this->dependencies,
			'content_handler'	=> $this->content_handler
		);
		$properties = '{{'.self::PROPS_TAG.'}}'.json_encode($properties).'{{/'.self::PROPS_TAG.'}}';

		return $properties . $this->contents;
	}

	protected function unprep_contents($payload)
	{
		$properties_end = strpos($payload, '{{/'.self::PROPS_TAG.'}}');
		if ($properties_end === FALSE)
		{
			throw new Cache_Exception('Incorrect formatting');
		}

		$this->contents = substr($payload, $properties_end + strlen('{{/'.self::PROPS_TAG.'}}'));
		$props = substr(substr($payload, 0, $properties_end), strlen('{{'.self::PROPS_TAG.'}}'));
		$props = json_decode($props, true);
		if ($props === NULL)
		{
			throw new Cache_Exception('Properties retrieval failed');
		}

		$this->created			= $props['created'];
		$this->expiration		= (int) ($props['expiration'] - time()) / 60;
		$this->dependencies		= $props['dependencies'];
		$this->content_handler	= $props['content_handler'];
	}

	public static function check_dependencies($dependencies)
	{
		foreach($dependencies as $dep)
		{
			$filemtime = filemtime($this->path.$dep.'.cache');
			if ($filemtime === FALSE || $filemtime > $this->created)
				return false;
		}
		return true;
	}

	public function delete()
	{
		$path = $this->path.$this->identifier.'.cache';
		@unlink($path);
		$this->reset();
	}

	public static function _delete_all($section)
	{
		// to be written
	}
}

/* End of file file.php */