<?php

class XmlController 
{
	protected $f3;
	protected $db;
	
	function __construct() 
	{
		$f3=Base::instance();
		$db=new DB\SQL(
			$f3->get('db_dns') . $f3->get('db_name'),
			$f3->get('db_user'),
			$f3->get('db_pass')
		);
		$this->f3=$f3;
		$this->db=$db;
	}
	
	/**
	* generate google sitemap
	*/
	public function sitemap()
	{
		$page=new Page($this->db, $this->f3->get("table_prefix"));
		$sitemapurls=$page->sitemappages();
		$this->f3->set('urls',$sitemapurls);
		header('Content-Type: text/xml; charset=UTF-8');
		$view=\View::instance();
		echo '<?xml version="1.0" encoding="UTF-8"?>'."\r\n";
		echo Template::instance()->render('/sitemap/sitemap.xml', 'application/xml');
		die;
	}
	
	/**
	* generate rss feed
	*/
	public function rssfeed()
	{
		$page=new Page($this->db, $this->f3->get("table_prefix"));
		$urls=$page->rsspages();
		$this->f3->set('urls',$urls);
		header('Content-Type: text/xml; charset=UTF-8');
		$view=\View::instance();
		echo '<?xml version="1.0" encoding="UTF-8"?>'."\r\n";
		echo Template::instance()->render('/rss/rss.xml', 'application/xml');
		die;
	}
}