<?php
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

class Autoloader {

	/**
	 * @var	array	Holds all the prefixes and paths
	 */
	protected $prefixes = array();

	/**
	 * @var	array	Holds all the class aliases
	 */
	protected $aliases = array();

	/**
	 * @var	array	Holds all the namespace paths
	 */
	protected $namespaces = array();

	/**
	 * @var	array	The default path to look in if the class is not in a package
	 */
	protected $default_path = NULL;

	/**
	 * Adds a package to the autoloader.  The prefix is the prefix for the
	 * package classes.
	 * 
	 * @access	public
	 * @param	string	the class name prefix
	 * @param
	 * @return	void
	 */
	public function add_prefix($prefix, $path)
	{
		$this->prefixes[$prefix] = $path;
	}

	/**
	 * Adds an array of packages to the autoloader
	 * 
	 * @access	public
	 * @param	array	the packages
	 * @return	void
	 */
	public function add_prefixes(array $prefixes)
	{
		$this->prefixes = array_merge($this->prefixes, $prefixes);
	}

	/**
	 * Adds a namespace and path
	 * 
	 * @access	public
	 * @param	string	the namespace
	 * @param	string	the path
	 * @return	void
	 */
	public function add_namespace($namespace, $path)
	{
		$this->namespaces[$namespace] = $path;
	}

	/**
	 * Adds an array of namespaces
	 * 
	 * @access	public
	 * @param	array	the namespaces
	 * @return	void
	 */
	public function add_namespaces(array $namespaces)
	{
		$this->namespaces = array_merge($this->namespaces, $namespaces);
	}


	/**
	 * Adds an alias for a class.
	 * 
	 * @access	public
	 * @param	string	the alias
	 * @param	string	class name
	 * @return	void
	 */
	public function add_alias($alias, $class, $is_abstract = false)
	{
		if ($is_abstract)
		{
			$class = array($class, true);
		}
		$this->aliases[strtolower($alias)] = $class;
	}

	/**
	 * Adds an array of class aliases.
	 * 
	 * @access	public
	 * @param	string	the alias
	 * @param	string	class name
	 * @return	void
	 */
	public function add_aliases(array $aliases)
	{
		$this->aliases = array_merge($this->aliases, array_change_key_case($aliases, CASE_LOWER));
	}

	/**
	 * Setsthe default path to look in, should the loader not find the file in
	 * any packages.
	 * 
	 * @access	public
	 * @param	string	the alias
	 * @param	string	class name
	 * @return	void
	 */
	public function default_path($path)
	{
		$this->default_path = $path;
	}

	public function register()
	{
		spl_autoload_register(array($this, 'load'));
	}

	public function load($class)
	{
		// Checks if there is a \ in the class name.  This indicates it is a
		// namespace.  It sets $pos to the position of the last \.
		if (($pos = strripos($class, '\\')) !== false)
		{
			$namespace = substr($class, 0, $pos);

			foreach ($this->namespaces as $ns => $path)
			{
				if (strncmp($ns, $namespace, $ns_len = strlen($ns)) === 0)
				{
					$class = substr($class, $pos + 1);
					$file_path = $path.str_replace('\\', DS, strtolower($namespace)).DS.str_replace('_', DS, strtolower($class)).'.php';
					if (file_exists($file_path))
					{
						require $file_path;
					}
					return true;
				}
			}
		}
		else
		{
			foreach ($this->prefixes as $prefix => $path)
			{
				if (strncmp($class, $prefix, strlen($prefix)) === 0)
				{
					$file_path = $path.str_replace('_', DS, strtolower($class)).'.php';
					if (is_file($file_path))
					{
						require $file_path;
						$this->_init_class($class);
					}
					return true;
				}
			}
		}

		// if we get here then lets just try to load it from the default path
		$file_path = $this->default_path.str_replace('_', DS, strtolower($class)).'.php';
		if (is_file($file_path))
		{
			require $file_path;
			$this->_init_class($class);
			return true;
		}

		// Still nothin? Lets see if its an alias then.
		if ($this->is_alias($class))
		{
			$this->create_alias_class($class);
			$this->_init_class($class);
			return true;
		}

		return false;
	}
	
	public function is_alias($class)
	{
		return array_key_exists(strtolower($class), $this->aliases);
	}
	
	public function create_alias_class($class)
	{
		class_alias($this->aliases[strtolower($class)], $class);
	}
	
	private function _init_class($class)
	{
		if (is_callable($class.'::_init'))
		{
			call_user_func($class.'::_init');
		}
	}
}

/* End of file autoloader.php */