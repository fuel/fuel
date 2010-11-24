<?php

use Fuel\Controller;

class Controller_Welcome extends Controller\Base {

	public function action_index()
	{
		$data['css'] = Asset::css(array('reset.css','960.css','main.css'));
		$data['controller_file'] = __FILE__;

		$bm = Benchmark::app_total();
		$data['exec_time'] = round($bm[0], 4);
		$data['mem_usage'] = round($bm[1] / pow(1024, 2), 4);

		$this->output = View::factory('welcome/index', $data);
	}

	public function action_404()
	{
		$data['css'] = Asset::css(array('reset.css','960.css','main.css'));
		$data['controller_file'] = __FILE__;

		$this->output = View::factory('welcome/404', $data);
	}
	
	public function action_hello()
	{
		$this->output = 'Hello '.$this->param('name');
	}
	
	public function action_pagination()
	{
		// DB Connection
		$db = mysql_connect('localhost', 'root', '');
		mysql_select_db('fuel');
		
		// Get total items
		$tsql = "SELECT * FROM `items`";
		$res = mysql_query($tsql, $db);
		$total_items = mysql_num_rows($res);

		$config = array(
			'total_items' => $total_items,
			'per_page' => 5,
			'pagination_url' => '/fuel/public/welcome/pagination/',
			'uri_segment' => 3,
			'num_links' => 5, // this is not required and is the number of links on each side of the current page
		);
		/**
		 * Method 1
		 *
		 * Create pagination by passing configs
		 * with an array.
		 */
		
		// Commented, using method below. can't use 2 methods
		Pagination::set_config($config);
		
		
		/**
		 * Method 2 
		 *
		 * Sets global configs that the pagination uses
		 */
		Config::set('pagination', $config);
		
		/**
		 * Get items from database
		 *
		 * Using Pagination::$current and Pagination::$per_page for LIMIT start, limit
		 * where $current is the current page and $per_page is the maximum items per page.
		 */
		$sql = "SELECT * FROM items";
		$sql .= " ORDER BY id";
		$sql .= " LIMIT " . Pagination::$offset . ", " . Pagination::$per_page;
		$resource = mysql_query($sql, $db);
		$items = array();
		
		while($data = mysql_fetch_assoc($resource))
		{
			$items[] = $data;
		}
		
		$data['items'] = $items;
		
		// Create links
		$data['pagination'] = Pagination::create_links();
		
		$data['css'] = Asset::css(array('reset.css','960.css','main.css'));
		$data['controller_file'] = __FILE__;
		
		$this->output = View::factory('welcome/pagination', $data);
	}
}
