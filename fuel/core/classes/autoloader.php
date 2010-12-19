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

namespace Fuel\App;

class Autoloader {

	/**
	 * @var	array	Holds all the prefixes and paths
	 */
	protected static $prefixes = array();

	/**
	 * @var	array	Holds all the class aliases
	 */
	protected static $aliases = array();

	/**
	 * @var	array	Holds all the namespace paths
	 */
	protected static $namespaces = array();

	/**
	 * @var	array	Holds all the namespace aliases
	 */
	protected static $namespace_aliases = array();

	/**
	 * @var	array	The default path to look in if the class is not in a package
	 */
	protected static $default_path = null;

	/**
	 * @var	bool	whether to initialize a loaded class
	 */
	protected static $auto_initialize = null;

	/**
	 * Adds a package to the autoloader.  The prefix is the prefix for the
	 * package classes.
	 *
	 * @access	public
	 * @param	string	the class name prefix
	 * @param
	 * @return	void
	 */
	public static function add_prefix($prefix, $path)
	{
		static::$prefixes[$prefix] = $path;
	}

	/**
	 * Adds an array of packages to the autoloader
	 *
	 * @access	public
	 * @param	array	the packages
	 * @return	void
	 */
	public static function add_prefixes(array $prefixes)
	{
		static::$prefixes = array_merge(static::$prefixes, $prefixes);
	}

	/**
	 * Returns the prefix's path or false when it doesn't exist
	 *
	 * @param  string
	 * @return array|bool
	 */
	public static function prefix_path($prefix)
	{
		if ( ! array_key_exists($prefix, static::$prefixes))
		{
			return false;
		}

		return static::$prefixes[$prefix];
	}

	/**
	 * Adds a namespace and path
	 *
	 * @access	public
	 * @param	string	the namespace
	 * @param	string	the path
	 * @return	void
	 */
	public static function add_namespace($namespace, $path)
	{
		static::$namespaces[$namespace] = $path;
	}

	/**
	 * Adds an array of namespaces
	 *
	 * @access	public
	 * @param	array	the namespaces
	 * @return	void
	 */
	public static function add_namespaces(array $namespaces, $prepend = false)
	{
		if ( ! $prepend)
		{
			static::$namespaces = array_merge(static::$namespaces, $namespaces);
		}
		else
		{
			static::$namespaces = $namespaces + static::$namespaces;
		}
	}


	/**
	 * Adds an alias for a class.
	 *
	 * @access	public
	 * @param	string	the alias
	 * @param	string	class name
	 * @return	void
	 */
	public static function add_alias($alias, $class)
	{
		static::$aliases[strtolower($alias)] = $class;
	}

	/**
	 * Adds an array of class aliases.
	 *
	 * @access	public
	 * @param	string	the alias
	 * @param	string	class name
	 * @return	void
	 */
	public static function add_aliases(array $aliases)
	{
		static::$aliases = array_merge(static::$aliases, array_change_key_case($aliases, CASE_LOWER));
	}

	/**
	 * Adds an alias for a namespace.
	 *
	 * @access	public
	 * @param	string	the alias
	 * @param	string	alias name
	 * @return	void
	 */
	public static function add_namespace_alias($alias, $namespace)
	{
		static::$namespace_aliases[$alias] = $namespace;
	}

	/**
	 * Adds an array of namespaces aliases.
	 *
	 * @access	public
	 * @param	array	the aliases
	 * @return	void
	 */
	public static function add_namespace_aliases(array $aliases, $prepend = false)
	{
		if ( ! $prepend)
		{
			static::$namespace_aliases = array_merge(static::$namespace_aliases, $aliases);
		}
		else
		{
			static::$namespace_aliases = $aliases + static::$namespace_aliases;
		}
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
	public static function add_path($path)
	{
		Fuel::add_path($path);
	}

	/**
	 * Register's the autoloader to the SPL autoload stack.
	 *
	 * @return	void
	 */
	public static function register()
	{
		spl_autoload_register('\Fuel\App\Autoloader::load', true, true);
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
	public static function load($class)
	{
		if (empty(static::$auto_initialize))
		{
			static::$auto_initialize = $class;
		}

		// Cleanup backslash prefix, messes up class_alias and other stuff
		$class = ltrim($class, '\\');

		if (Fuel::$path_cache != null && array_key_exists($class, Fuel::$path_cache))
		{
			require Fuel::$path_cache[$class];
			static::_init_class($class);
			return true;
		}

		// Checks if there is a \ in the class name.  This indicates it is a
		// namespace.  It sets $pos to the position of the last \.
		if (($pos = strripos($class, '\\')) !== false)
		{
			$namespace = substr($class, 0, $pos);

			foreach (static::$namespaces as $ns => $path)
			{
				if (strncmp($ns, $namespace, $ns_len = strlen($ns)) === 0)
				{
					$class_no_ns = substr($class, $pos + 1);

					$file_path = strtolower($path.substr($namespace, strlen($ns) + 1).DS.str_replace('_', DS, $class_no_ns).'.php');
					if (file_exists($file_path))
					{
						Fuel::$path_cache[$class] = $file_path;
						Fuel::$paths_changed = true;
						require $file_path;
						static::_init_class($class);
						return true;
					}
				}
			}
			if (static::namespace_alias($class))
			{
				static::_init_class($class);
				return true;
			}
		}
		else
		{
			if (static::is_alias($class))
			{
				static::create_alias_class($class);
				static::_init_class($class);
				return true;
			}


			foreach (static::$prefixes as $prefix => $path)
			{
				if (strncmp($class, $prefix, strlen($prefix)) === 0)
				{
					$file_path = $path.str_replace('_', DS, strtolower($class)).'.php';
					if (is_file($file_path))
					{
						Fuel::$path_cache[$class] = $file_path;
						Fuel::$paths_changed = true;
						require $file_path;
						static::_init_class($class);
						return true;
					}
				}
			}
		}

		$file_path = Fuel::find_file('classes', $class);

		if ($file_path !== false)
		{
			Fuel::$path_cache[$class] = $file_path;
			Fuel::$paths_changed = true;
			require $file_path;
			static::_init_class($class);
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
	public static function is_alias($class)
	{
		return array_key_exists(strtolower($class), static::$aliases);
	}

	/**
	 * Checks to see if the namespace of the given class is an alias for another
	 * namespace.  If it is then create the alias class.
	 *
	 * @param	string	the class name
	 * @return	bool	whether it was an alias
	 */
	public static function namespace_alias($class)
	{
		foreach (static::$namespace_aliases as $alias => $actual)
		{
			if ($alias == '')
			{
				continue;
			}
			if (strpos($class, $alias) === 0)
			{
				$alias = $actual.substr($class, strlen($alias));
				if (class_exists($alias))
				{
					class_alias($alias, $class);
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Creates an alias class for the given class name.
	 *
	 * @param	string	the class name
	 */
	public static function create_alias_class($class)
	{
		class_alias(static::$aliases[strtolower($class)], $class);
	}

	/**
	 * Checks to see if the given class has a static _init() method.  If so then
	 * it calls it.
	 *
	 * @param	string	the class name
	 */
	private static function _init_class($class)
	{
		if (static::$auto_initialize === $class)
		{
			static::$auto_initialize = null;
			if (is_callable($class.'::_init'))
			{
				call_user_func($class.'::_init');
			}
		}
	}
}

/* End of file autoloader.php */