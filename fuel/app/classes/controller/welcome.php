<?php
/**
 * An example Controller.  This shows the most basic usage of a Controller.
 */
class Controller_Welcome extends Controller {

	public function action_index()
	{
		//$this->render('welcome/index');
		
		$config = array(
			'pagination_url' => \Uri::create('welcome/index'),
			'total_items' => 17,
			'per_page' => 5,
			'uri_segment' => 3,
		);
	/*
		$config = array(
			'pagination_url' => \Uri::create('welcome/index/{p}?foo=bar'),
			'total_items' => 17,
			'per_page' => 5,
			'uri_segment' => 3,
			'method' => 'segment_tag',
			
			//hide the segment when page_nr == 1 
			//'hide_1' => false, //default = true
			
			//set the tag that will be str_replaced with the page_nr in the pagination_url
			//'replacement_tag' => ':page', // default ='{p}'
		);
		
		*/
		
		
	/*
		$config = array(
			'pagination_url' => \Uri::create('welcome/index?{p}&foo=bar'),
			'total_items' => 17,
			'per_page' => 5,
			'method' => 'get_tag',
		
			// set the get variable name:
			//'get_variable'  => 'pagina', //default = 'page'
			
			//hide the get variable when page_nr == 1 
			//'hide_1' => false, //default = true
			
				//set the tag that will be str_replaced with the page_nr in the pagination_url
			//'replacement_tag' => ':page', // default ='{p}'
		);
	*/
		
		Pagination::set_config($config);
		echo Pagination::create_links();
	}

	public function action_404()
	{
		// Set a HTTP 404 output header
		Output::$status = 404;
		$this->render('welcome/404');
	}
}