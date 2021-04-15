<?php

class Controller {

	protected $f3;
	protected $db;
	
	function beforeroute() 
	{
		if( NULL !== $this->f3->get('SESSION') && NULL === $this->f3->get('SESSION.csrf')) {
			$this->f3->set("SESSION.csrf",$this->f3->session->csrf());
		}
		if ($this->f3->VERB==='POST')
		{
			if(  $this->f3->get('POST.session_csrf') !==  $this->f3->get('SESSION.csrf') ) 
			{	// possible CSRF attack
				$logger = new Log('logs/'.date("Ymd").'error.log');
				$logger->write("CSRF error on page ".$this->f3->get('PARAMS.0').": posted csrf ".$this->f3->get('POST.session_csrf') ."!== session csrf". $this->f3->get('SESSION.csrf'), 'r');
				$this->f3->error(403); 
			}
		}
		
		$menu=new Page($this->db, $this->f3->get("table_prefix"));
		$this->f3->set("layout", $menu->getMenu());

		$access=Access::instance();
		$access->policy('allow'); // allow access to all routes by default
		$access->deny('/'.$this->f3->get("adminpage").'*');
		
		// admin routes
		$access->allow('/'.$this->f3->get("adminpage").'*','100'); //100 = admin  ; 1 = regular user
		$access->deny('/user*');
		// user login routes
		$access->allow('/user*',['100','1']);
		$usertype=$this->f3->exists('SESSION.user_type') ? $this->f3->get('SESSION.user_type') : 0;

		$access->authorize($usertype);
	}

	function afterroute() {
		echo Template::instance()->render('layout.htm');
	}

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
	* use this function as a basic antispam measure. 
	* Check for common spammy strings and block them.
	*/
	public function antispam($post)
	{
		$this_is_spam=false;
		$filters=array( "talkwithcustomer", "cialis","viagra","girls for sex","girl for the night","woman for the night","women for sex","sexy girls", "bitcoin","cryptocurrency", "passive income", "win an iphone", "free iphone", "win iphone", "iphone giveaway","dating site","sex dating","sex with girls","sexygirls","adultdating","sexy girls","adult dating", "business leads", "more visitors", "advertising","talkwithwebvisitor","Маkе Moneу","Pаssivе Income", "mail.ru", "earning money","goo-gl.ru.com","clickfrm.com", "investment instrument","url=","https://","http://");
		foreach ( $post as $post ) {
		  foreach ( $filters as $filter) {
			if ( strpos ( strtolower($post) , strtolower($filter) ) !== FALSE ) {
				 $this_is_spam=true;
			}
		  }
		}
		return $this_is_spam;
	}
}
