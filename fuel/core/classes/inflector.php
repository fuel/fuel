<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Dan Horrigan <http://dhorrigan.com>
 * @license		Apache License v2.0
 * @copyright	2010 Dan Horrigan
 */

namespace Fuel;

/**
 * Some of this code was written by Flinn Mueller.
 *
 * @copyright Flinn Mueller
 */
class Inflector {

	protected static $uncountable_words = array(
		'equipment', 'information', 'rice', 'money',
		'species', 'series', 'fish'
	);

	protected static $plural_rules = array(
		'/^(ox)$/'					=> '\1\2en',	// ox
		'/([m|l])ouse$/'			=> '\1ice',		// mouse, louse
		'/(matr|vert|ind)ix|ex$/'	=> '\1ices',	// matrix, vertex, index
		'/(x|ch|ss|sh)$/'			=> '\1es',		// search, switch, fix, box, process, address
		'/([^aeiouy]|qu)y$/'		=> '\1ies',		// query, ability, agency
		'/(hive)$/'					=> '\1s',		// archive, hive
		'/(?:([^f])fe|([lr])f)$/'	=> '\1\2ves',	// half, safe, wife
		'/sis$/'					=> 'ses',		// basis, diagnosis
		'/([ti])um$/'				=> '\1a',		// datum, medium
		'/(p)erson$/'				=> '\1eople',	// person, salesperson
		'/(m)an$/'					=> '\1en',		// man, woman, spokesman
		'/(c)hild$/'				=> '\1hildren',	// child
		'/(buffal|tomat)o$/'		=> '\1\2oes',	// buffalo, tomato
		'/(bu)s$/'					=> '\1\2ses',	// bus
		'/(alias|status|virus)/'	=> '\1es',		// alias
		'/(octop)us$/'				=> '\1i',		// octopus
		'/(ax|cri|test)is$/'		=> '\1es',		// axis, crisis
		'/s$/'						=> 's',			// no change (compatibility)
		'/$/'						=> 's',
	);

	protected static $singular_rules = array(
		'/(matr)ices$/'			=> '\1ix',
		'/(vert|ind)ices$/'		=> '\1ex',
		'/^(ox)en/'				=> '\1',
		'/(alias)es$/'			=> '\1',
		'/([octop|vir])i$/'		=> '\1us',
		'/(cris|ax|test)es$/'	=> '\1is',
		'/(shoe)s$/'			=> '\1',
		'/(o)es$/'				=> '\1',
		'/(bus)es$/'			=> '\1',
		'/([m|l])ice$/'			=> '\1ouse',
		'/(x|ch|ss|sh)es$/'		=> '\1',
		'/(m)ovies$/'			=> '\1\2ovie',
		'/(s)eries$/'			=> '\1\2eries',
		'/([^aeiouy]|qu)ies$/'	=> '\1y',
		'/([lr])ves$/'			=> '\1f',
		'/(tive)s$/'			=> '\1',
		'/(hive)s$/'			=> '\1',
		'/([^f])ves$/'			=> '\1fe',
		'/(^analy)ses$/'		=> '\1sis',
		'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/' => '\1\2sis',
		'/([ti])a$/'			=> '\1um',
		'/(p)eople$/'			=> '\1\2erson',
		'/(m)en$/'				=> '\1an',
		'/(s)tatuses$/'			=> '\1\2tatus',
		'/(c)hildren$/'			=> '\1\2hild',
		'/(n)ews$/'				=> '\1\2ews',
		'/s$/'					=> '',
	);

	/**
	 * Gets the plural version of the given word
	 *
	 *
	 * @param	string	$word	the word to pluralize
	 * @return	string	the plural version of $word
	 */
	public static function pluralize($word)
	{
		$result = strval($word);

		if ( ! static::is_countable($result))
		{
			return $result;
		}

		foreach (static::$plural_rules as $rule => $replacement)
		{
			if (preg_match($rule, $result))
			{
				$result = preg_replace($rule, $replacement, $result);
				break;
			}
		}

		return $result;
	}

	/**
	 * Gets the singular version of the given word
	 *
	 *
	 * @param	string	$word	the word to singularize
	 * @return	string	the singular version of $word
	 */
	public static function singularize($word)
	{
		$result = strval($word);

		if ( ! static::is_countable($result))
		{
			return $result;
		}

		foreach (static::$singular_rules as $rule => $replacement)
		{
			if (preg_match($rule, $result))
			{
				$result = preg_replace($rule, $replacement, $result);
				break;
			}
		}

		return $result;
	}

	/**
	 * Takes a string that has words seperated by underscores and turns it into
	 * a CamelCased string.
	 *
	 * @param	strng	$underscored_word	the underscored word
	 * @return	string	the CamelCased version of $underscored_word
	 */
	public static function camelize($underscored_word)
	{
		return preg_replace('/(^|_)(.)/e', "strtoupper('\\2')", strval($underscored_word));
	}

	/**
	 * Takes a CamelCased string and returns an underscore separated version.
	 *
	 * @param	string	$camel_cased_word	the CamelCased word
	 * @return	string	an underscore separated version of $camel_cased_word
	 */
	public static function underscore($camel_cased_word)
	{
		return strtolower(preg_replace('/([A-Z]+)([A-Z])/', '\1_\2', preg_replace('/([a-z\d])([A-Z])/', '\1_\2', strval($camel_cased_word))));
	}

	/**
	 * Turns an underscore separated word and turns it into a human looking string.
	 *
	 * @param	string	$lower_case_and_underscored_word	the word
	 * @return	string	the human version of $lower_case_and_underscored_word
	 */
	public static function humanize($lower_case_and_underscored_word)
	{
		return ucfirst(strtolower(ereg_replace('_', " ", strval($lower_case_and_underscored_word))));
	}

	/**
	 * Takes the class name out of a modulized string.
	 *
	 * @param	string	$class_name_in_module	the modulized class
	 * @return	string	the string without the class name
	 */
	public static function demodulize($class_name_in_module)
	{
		return preg_replace('/^.*::/', '', strval($class_name_in_module));
	}

	/**
	 * Takes a class name and determines the table name.  The table name is a
	 * pluralized version of the class name.
	 *
	 * @param	string	$class_name the table name
	 * @return	string	the table name
	 */
	public static function tableize($class_name)
	{
		return static::pluralize(static::underscore($class_name));
	}

	/**
	 * Takes a table name and creates the class name.
	 *
	 * @param	string	$table_name	the table name
	 * @return	string	the class name
	 */
	public static function classify($table_name)
	{
		return static::camelize(static::singularize($table_name));
	}

	/**
	 * Gets the foreign key for a given class.
	 * 
	 * @param	string	$class_name		the class name
	 * @param	bool	$use_underscore	whether to use an underscore or not
	 * @return	string	the foreign key
	 */
	public static function foreign_key($class_name, $use_underscore = true)
	{
		return static::underscore(static::demodulize($class_name)).($use_underscore ? "_id" : "id");
	}

	/**
	 * Checks if the given word has a plural version.
	 *
	 * @param	string	the word to check
	 * @return	bool	if the word is countable
	 */
	public static function is_countable($word)
	{
		return ! (\in_array(\strtolower(\strval($word)), static::$uncountable_words));
	}
}

/* End of file inflector.php */