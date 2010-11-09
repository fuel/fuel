<?php defined('COREPATH') or exit('No direct script access allowed');

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

class Fuel_Pagination {
	
	/**
	 * @var	integer	The current page
	 */
	public static $current;
	
	/**
	 * @var	integer	The number of items per page
	 */
	public static $per_page;
	
	/**
	 * @var	array	The pagination variables
	 */
	protected static $page_vars;
	
	/**
	 * @var	integer	The total number of items
	 */
	protected static $total_rows;
	
	/**
	 * @var	integer	The URI segment containg page number
	 */
	protected static $uri_segment;
	
	/**
	 * @var	mixed	The pagination URL
	 */
	protected static $pagination_url;
	
	// --------------------------------------------------------------------

	/**
	 * Init
	 *
	 * Loads in the config and sets the variables
	 *
	 * @access	public
	 * 
	 * @return	void
	 */
	public static function init()
	{
		self::$per_page = Config::get('per_page');
		self::$total_rows = Config::get('total_rows');
		self::$pagination_url = Config::get('pagination_url');
		self::$uri_segment = Config::get('uri_segment');
		
		if ( self::$uri_segment == NULL )
		{
			self::$uri_segment = 1;
		}
		
		self::$current	 = self::$uri_segment - 1;
		self::$page_vars = self::make(self::$total_rows, self::$per_page, self::$uri_segment);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Set Config
	 *
	 * Sets the configuration for pagination
	 *
	 * @access public

	 * @param array   $config The configuration array
	 * 
	 * @return void
	 */
	public static function set_config(array $config)
	{
		isset($config['per_page']) and self::$per_page = $config['per_page'];
		isset($config['total_rows']) and self::$total_rows = $config['total_rows'];
		isset($config['pagination_url']) and self::$pagination_url = $config['pagination_url'];
		isset($config['uri_segment']) and self::$uri_segment = $config['uri_segment'];
		
		self::$current	 = self::$uri_segment - 1;
		self::$page_vars = self::make(self::$total_rows, self::$per_page, self::$uri_segment);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Make
	 *
	 * Prepares vars for creating links
	 *
	 * @access public
	 * 
	 * @param integer $total_items    The total number of items
	 * @param integer $items_per_page The number of items per page
	 * @param integer $p              The current page
	 * 
	 * @return array    The pagination variables
	 */
	public static function make($total_items, $items_per_page, $p)
	{
		if( !$items_per_page )
		{
			$items_per_page = 1;
		}
			
		$maxpage = ceil($total_items / $items_per_page);
		
		if( $maxpage <= 0 )
		{
			$maxpage = 1;
		}
		
		$p = ( ($p > $maxpage) ? $maxpage : ( ($p < 1) ? 1 : $p ) );
		$start = ($p - 1) * $items_per_page;
		
		return array($start, $p, $maxpage);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Create Links
	 *
	 * Creates the pagination links
	 *
	 * @access public
	 * 
	 * @return mixed    The pagination links
	 */
	public static function create_links()
	{
		$page_array = array();
		
		for($i = 0; $i <= self::$page_vars[2] - 1; $i++)
		{
			if( $i + 1 == self::$page_vars[1] )
			{
				$link = '1';
			}
			else
			{
				$link = '0';
			}
			
			$page_array[$i] = array('page' => $i + 1, 'link' => $link);
		}
		
		$pagination = '';
	
		if ( self::$uri_segment == 1 )
		{ 
			$pagination .= '&laquo; Previous&nbsp;|&nbsp;'; 
		}
		else
		{ 
			$previous = self::$uri_segment - 1;
			$pagination .= '<a href="' . self::$pagination_url . $previous . '">&laquo; Previous</a>';
			$pagination .= '&nbsp;|&nbsp;'; 
		}
		
		foreach ($page_array as $browse)
		{
			if ( $browse['link'] == 1 )
			{
				$pagination .= '<b>'.$browse['page'].'</b>&nbsp;';
			}
			else
			{
				$pagination .= '<a href="' . self::$pagination_url . $browse['page'] . '">';
				$pagination .= $browse['page'].'</a>&nbsp;';
			}
		}

		if ( self::$page_vars[2] == self::$uri_segment )
		{ 
			$pagination .= '&nbsp;|&nbsp;Next &raquo;'; 
		}
		else
		{ 
			$next = self::$uri_segment + 1; 
			$pagination .= '&nbsp;|&nbsp;';
			$pagination .= '<a href="' . self::$pagination_url . $next . '">Next &raquo;</a>';
		}
		
		if ( self::$total_rows > 1 )
		{
			return $pagination; 
		}
		else
		{
			return NULL;
		}
	}
}

/* End of file pagination.php */