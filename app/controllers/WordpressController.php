<?php

class WordpressController extends Controller 
{
	public function error()
	{
		$logger = new Log('logs/'.date("Ymd").'wp-login.log');
		$logger->write($this->f3->get('PARAMS.0') ." ".$_SERVER["HTTP_USER_AGENT"], 'r');
		
		$page=new Page($this->db, $this->f3->get("table_prefix"));
		$page->longtitle="You shall not pass";
		$page->content="This page is not available for you! Please leave.";
		$this->f3->set('page',$page); 
		$this->f3->set('view','page/show_page.htm');
	}
}