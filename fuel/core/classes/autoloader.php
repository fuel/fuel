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
	 * @var	array	Holds all the namespace aliases
	 */
	protected $namespace_aliases = array();

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
	public function add_alias($alias, $class)
	{
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
	 * Adds an alias for a namespace.
	 * 
	 * @access	public
	 * @param	string	the alias
	 * @param	string	alias name
	 * @return	void
	 */
	public function add_namespace_alias($alias, $namespace)
	{
		$this->namespace_aliases[$alias] = $namespace;
	}

	/**
	 * Adds an array of namespaces aliases.
	 * 
	 * @access	public
	 * @param	array	the aliases
	 * @return	void
	 */
	public function add_namespace_aliases(array $aliases)
	{
		$this->namespace_aliases = array_merge($this->namespace_aliases, $aliases);
	}

	/**
	 * Sets the default path to look in, should the loader not find the file in
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

	/**
	 * Register's the autoloader to the SPL autoload stack.
	 *
	 * @return	void
	 */
	public function register()
	{
		spl_autoload_register(array($this, 'load'), true, true);
	}

	/**
	 * Loads the given class by first determining if it is a namespaced class
	 * or a class in the global namespace.  If it cannot find the class this way
	 * then it tries to load it from the default path.  Next it sees if the
	 * class is an alias of another class.  If so, it will create a class alias.
	 * Finally it checks to see if the namespace of the class is an alias of
	 * another namespace.  If all that fails then it returns false.
	 *
	 * @param	string	the class name
	 * @return	bool	if the class has been loaded
	 */
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
					$class_no_ns = substr($class, $pos + 1);
					$file_path = $path.str_replace('\\', DS, strtolower($namespace)).DS.str_replace('_', DS, strtolower($class_no_ns)).'.php';
					if (file_exists($file_path))
					{
						require $file_path;
						$this->_init_class($class);
						return true;
					}
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
						return true;
					}
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

		if ($this->namespace_alias($class))
		{
			$this->_init_class($class);
			return true;
		}

		return false;
	}

	/**
	 * Checks to see if the given class in an alias of another class.
	 *
	 * @param	string	the class name
	 * @return	bool	if the class name is an alias
	 */
	public function is_alias($class)
	{
		return array_key_exists(strtolower($class), $this->aliases);
	}

	/**
	 * Checks to see if the namespace of the given class is an alias for another
	 * namespace.  If it is then create the alias class.
	 *
	 * @param	string	the class name
	 * @return	bool	whether it was an alias
	 */
	public function namespace_alias($class)
	{
		foreach ($this->namespace_aliases as $alias => $actual)
		{
			if ($alias == '')
			{
				continue;
			}
			if (strpos($class, $alias) === 0)
			{
				$alias = $actual.substr($class, strlen($actual) - 1);
				class_alias($alias, $class);
				return true;
			}
		}
		return false;
	}

	/**
	 * Creates an alias class for the given class name.
	 *
	 * @param	string	the class name
	 */
	public function create_alias_class($class)
	{
		class_alias($this->aliases[strtolower($class)], $class);
	}

	/**
	 * Checks to see if the given class has a static _init() method.  If so then
	 * it calls it.
	 * 
	 * @param	string	the class name
	 */
	private function _init_class($class)
	{
		if (is_callable($class.'::_init'))
		{
			call_user_func($class.'::_init');
		}
	}
}

/* End of file autoloader.php */