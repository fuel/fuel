<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package		Fuel
 * @version		1.0
 * @author		Dan Horrigan <http://dhorrigan.com>
 * @license		MIT License
 * @copyright	2010 - 2011 Fuel Development Team
 */

namespace Fuel\Core;



class Pagination {

	/**
	 * @var	integer	The current page
	 */
	public static $current_page = null;

	/**
	 * @var	integer	The offset that the current page starts at
	 */
	public static $offset = 0;

	/**
	 * @var	integer	The number of items per page
	 */
	public static $per_page = 10;

	/**
	 * @var	integer	The number of total pages
	 */
	public static $total_pages = 0;

	/**
	 * @var	integer	The total number of items
	 */
	protected static $total_items = 0;

	/**
	 * @var	integer	The total number of links to show
	 */
	protected static $num_links = 5;

	/**
	 * @var	integer	The URI segment containg page number
	 */
	protected static $uri_segment = 3;

	/**
	 * @var	mixed	The pagination URL
	 */
	protected static $pagination_url;

	
	/**
	 * @var	bool	Hide pagination nr when method == SEGMENT_TAG / GET_TAG and page_nr == 1 (not supported in static::CLASSIC)
	 */
	protected static $hide_1 = true;
	
	/**
	 * @var	string	the functioning mode of the class is dictated by this 
	 */
	protected static $method = 'classic'; // static::CLASSIC
	
	const CLASSIC = 'classic';
	const SEGMENT_TAG = 'segment_tag';
	const GET_TAG = 'get_tag';


	/**
	 * @var	string	To avoid confusing settings when method == segment_tag | get_tag, the uri will be set in $uri, not in $pagination_url
	 */
	public static $uri;	

	/**
	 * @var	array	When creating the pagination links, this will be passed to Uri::create() as the second parameter
	 */
	public static $variables = array();
	
	/**
	 * @var	array	When creating the pagination links, this will be passed to Uri::create() as the third parameter
	 */
	public static $get_variables = array();
	
	/**
	 * @var	string	NOTICE: in $uri this must be preceded by colon: ':page' eg: 'monkeys/index/:page' 
	 * this is because of how Uri::create works.
	 * The segment placeholder when method == SEGMENT_TAG, 
	 * eg: monkeys/index/:page, or if method == GET_TAG, the get var name
	 */
	public static $variable_name = 'page';	

	/**
	 * Init
	 *
	 * Loads in the config and sets the variables
	 *
	 * @access	public
	 * @return	void
	 */
	public static function _init()
	{
		$config = \Config::get('pagination', array());

		static::set_config($config);
	}

	// --------------------------------------------------------------------

	/**
	 * Set Config
	 *
	 * Sets the configuration for pagination
	 *
	 * @access public
	 * @param array   $config The configuration array
	 * @return void
	 */
	public static function set_config(array $config)
	{

		foreach ($config as $key => $value)
		{
			static::${$key} = $value;
		}

		static::initialize();
	}

	// --------------------------------------------------------------------

	/**
	 * Prepares vars for creating links
	 *
	 * @access public
	 * @return array    The pagination variables
	 */
	protected static function initialize()
	{

		static::$total_pages = ceil(static::$total_items / static::$per_page) ?: 1;

		static::$current_page = static::current_page();

		if (static::$current_page > static::$total_pages)
		{
			static::$current_page = static::$total_pages;
		}
		elseif (static::$current_page < 1)
		{
			static::$current_page = 1;
		}

		// The current page must be zero based so that the offset for page 1 is 0.
		static::$offset = (static::$current_page - 1) * static::$per_page;
	}

	// --------------------------------------------------------------------

	/**
	 * Creates the pagination links
	 *
	 * @access public
	 * @return mixed    The pagination links
	 */
	public static function create_links()
	{
		if (static::$total_pages == 1)
		{
			return '';
		}

		$pagination = '';

		// Let's get the starting page number, this is determined using num_links
		$start = ((static::$current_page - static::$num_links) > 0) ? static::$current_page - (static::$num_links - 1) : 1;

		// Let's get the ending page number
		$end   = ((static::$current_page + static::$num_links) < static::$total_pages) ? static::$current_page + static::$num_links : static::$total_pages;

		$pagination .= '&nbsp;'.static::prev_link('&laquo Previous').'&nbsp;&nbsp;';

		for($i = $start; $i <= $end; $i++)
		{
			if (static::$current_page == $i)
			{
				$pagination .= '<b>'.$i.'</b>';
			}
			else
			{
				$pagination .= \Html::anchor(static::create_link_url($i), $i);
			}
		}

		$pagination .= '&nbsp;'.static::next_link('Next &raquo;');

		return $pagination;
	}

	// --------------------------------------------------------------------

	/**
	 * Pagination "Next" link
	 *
	 * @access public
	 * @param string $value The text displayed in link
	 * @return mixed    The next link
	 */
	public static function next_link($value)
	{
		if (static::$total_pages == 1)
		{
			return '';
		}

		if (static::$current_page == static::$total_pages)
		{
			return $value;
		}
		else
		{
			$next_page = static::$current_page + 1;
			return \Html::anchor(static::create_link_url($next_page), $value);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Pagination "Previous" link
	 *
	 * @access public
	 * @param string $value The text displayed in link
	 * @return mixed    The previous link
	 */
	public static function prev_link($value)
	{
		if (static::$total_pages == 1)
		{
			return '';
		}

		if (static::$current_page == 1)
		{
			return $value;
		}
		else
		{
			$previous_page = static::$current_page - 1;
			return \Html::anchor(static::create_link_url($previous_page), $value);
		}
	}
	
	// --------------------------------------------------------------------

	/**
	 * Create the the link url from page_nr and pagination_url via the configured $method
	 *
	 * @access public
	 * @param string $page_nr The page nr for the url
	 * @return string    The pagination_url
	 */
	public static function create_link_url($page_nr)
	{
		// make local copyes to mess around with (and remove the static:: stuff so I can actually read the code :P )
		$variables = static::$variables;
		$get_variables = static::$get_variables;
		$uri = static::$uri;
		$variable_name = static::$variable_name;
		$paceholder_variable = ':'.$variable_name;
		
		switch (strtolower(static::$method)) 
		{
			case static::SEGMENT_TAG:
			
				if($page_nr == 1 AND static::$hide_1 === true)
				{
					//the $variables[':page'] should not be set anyway, but just in case: 
					if (isset($variables[$variable_name])) 
					{
						unset($variables[$variable_name]);
					}
					
					//remove ':page/' (in case there are other segments after it), 
					//or just ':page' if there is no slash after it (and therefor no other segments)
					$uri = str_replace(array($paceholder_variable.'/', $paceholder_variable), '', $uri);
				}
				else
				{
					$variables = array_merge($variables, array($variable_name => $page_nr)); 

					// there better be a ':page' or equivalent in $uri
					if (false === strpos($uri, $variable_name)) 
					{
						\Error::notice('Pagination::create_link_url(): The Pagination::$variable_name:"'.$variable_name.'" is missing from Pagination::$uri: "'.$uri.'"');
						return '#';
					}
				}
				
				return \Uri::create($uri, $variables, $get_variables);
				
		  	case static::GET_TAG:
		  		
		  		if($page_nr == 1 AND static::$hide_1 === true)
				{
					//the $get_variables[':page'] should not be set anyway, but just in case: 
					if (isset($get_variables[$variable_name]))
					{
						unset($get_variables[$variable_name]);
					}
				}
				else
				{
					$get_variables = array_merge($get_variables, array($variable_name => $page_nr)); 
				}
				return \Uri::create($uri, $variables, $get_variables);

			default:	
				//fall back to classic
				\Error::notice('The value of Pagination::$method is configured with an unknown and unsuported method: "'.static::$method.'". Falling back to "classic" method');
				
			case static::CLASSIC:
				$page_nr = ($page_nr == 1) ? '' : '/'.$page_nr;
				return rtrim(static::$pagination_url, '/').$page_nr;
		}	
	}
	
	/**
	 * Get current page from uri with the configured static::$method
	 *
	 * @access public
	 * @return int    The page nr if present in the request url
	 */
	public static function current_page()
	{
		switch (strtolower(static::$method)) 
		{
			default:
				\Error::notice('The value of Pagination::$method is configured with an unknown and unsuported method: "'.static::$method.'". Falling back to "classic" method');
			case static::CLASSIC:
		  	case static::SEGMENT_TAG:
		  		return (int) \URI::segment(static::$uri_segment);
		  	case static::GET_TAG:
		  		return (int) \Input::get(static::$variable_name, 1);
  		}
	}
	
}

/* End of file pagination.php */
