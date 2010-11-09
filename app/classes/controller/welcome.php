<?php

class Controller_Welcome extends Controller {

	public function action_index()
	{
		$data['css'] = Asset::css(array('reset.css','960.css','main.css'));
		$data['controller_file'] = str_replace(DOCROOT, '', __FILE__);
		
		$this->output = View::factory('welcome/index', $data);
	}
	
	public function action_url()
	{
		$this->output = View::factory('welcome/url');
	}
	
	public function action_pagination()
	{
		// temporary db connection
		$db = mysql_connect('localhost', 'root', 'am*12345');
		mysql_select_db('clients');
		
		// get total users (sample)
		$tsql = "SELECT * FROM acid_users";
		$res = mysql_query($tsql, $db);
		$total_users = mysql_num_rows($res);
		
		Pagination::set_config(array(
			'total_rows' => $total_users,
			'per_page' => 1,
			'pagination_url' => '/fuel/welcome/pagination/',
			'uri_segment' => URI::segment(3),
		));
		
		Config::set('pagination_url', '/fuel/welcome/pagination/');
		Config::set('total_rows', $total_users);
		Config::set('per_page', 1);
		Config::set('uri_segment', URI::segment(3));
		
		// get users
		$sql = "SELECT * FROM acid_users";
		$sql .= " ORDER BY id";
		$sql .= " LIMIT " . Pagination::$current . ", " . Pagination::$per_page;
		$resource = mysql_query($sql, $db);
		$users = array();
		
		while($data = mysql_fetch_assoc($resource))
		{
			$users[] = $data;
		}
		
		$data['users'] = $users;
		
		// create pagination links
		$data['pagination'] = Pagination::create_links();
		
		$this->output = View::factory('welcome/pagination', $data);
	}

	public function action_404()
	{
		$data['css'] = Asset::css(array('reset.css','960.css','main.css'));
		$data['controller_file'] = str_replace(DOCROOT, '', __FILE__);

		$this->output = View::factory('welcome/404', $data);
	}
}
