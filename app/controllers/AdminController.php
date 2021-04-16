<?php

class AdminController extends Controller
{	

	function afterroute() {
		echo Template::instance()->render('/admin/adminlayout.htm');
		die;
	}
	
	/**
	* metatags: add to pages and show pages based on metatags
	*/
	public function metatag_pages()
	{
		$prefix=$this->f3->get("table_prefix");
		$metatags=new MetaTags($this->db, $prefix);
		$this->f3->set('metatags',$metatags->pages($this->f3->get("PARAMS.id")));	
		$this->f3->set('view','admin/metatagpagelist.htm');
	}
	
	public function metatags()
	{
		$prefix=$this->f3->get("table_prefix");

		$metatags=new MetaTags($this->db, $prefix);
		if($this->f3->VERB==="POST")
		{
			$metatags->add($this->f3->get("POST"));
		}

		$this->f3->set('metatags',$metatags->all());	
		$this->f3->set('view','admin/metatagslist.htm');
	}
	public function delete_metatag()
	{
		$prefix=$this->f3->get("table_prefix");

		$metatags=new MetaTags($this->db, $prefix);
		$metatags->delete($this->f3->get("PARAMS.id"));
		$this->f3->reroute($this->f3->get("adminpage")."/metatags"); 
		
	}
	
	/**
	* news: show on homepage and/or news pages
	*/
	public function news()
	{	
		$news=new News($this->db, $this->f3->get("table_prefix"));
		$articles=new News($this->db, $this->f3->get("table_prefix"));
		
		$this->f3->set('news',$news->all("news",0));
		$this->f3->set('articles',$articles->latest(200,false,0));
		$this->f3->set('view','admin/newslist.htm');
	}
	
	public function delete_news()
	{	
		$news=new News($this->db, $this->f3->get("table_prefix"));
		$news->delete($this->f3->get("PARAMS.id"));
		$this->f3->reroute('/'.$this->f3->get("adminpage").'/news');
	}
	
	public function add_news()
	{	
		if($this->f3->VERB==="POST")
		{
			$news=new News($this->db, $this->f3->get("table_prefix"));
			$news->add($this->f3->get("POST"));
			$this->f3->reroute('/'.$this->f3->get("adminpage").'/news');
		}
		$this->f3->set('view','admin/news.htm');
	}
	
	public function edit_news()
	{	
		$news=new News($this->db, $this->f3->get("table_prefix"));
		$news->getById($this->f3->get('PARAMS.id'));
		if($this->f3->VERB==="POST")
		{
			$news->edit($this->f3->get('PARAMS.id'), $this->f3->get("POST"));
			$this->f3->reroute('/'.$this->f3->get("adminpage").'/news');
		}
		$this->f3->set('news',$news);
		$this->f3->set('view','admin/news.htm');
	}
	
	/**
	* user management
	*/
	public function delete_user()
	{	
		$user=new User($this->db, $this->f3->get("table_prefix"));
		if($user->delete($this->f3->get("PARAMS.id"))){
			$this->f3->reroute('/'.$this->f3->get("adminpage").'/users');
		}
		else {
			echo "ERROR: This user can not be deleted";die;
		}
	}
	
	public function users()
	{	
		$users = new User($this->db, $this->f3->get("table_prefix"));
		$this->f3->set('users',$users->all());
		$this->f3->set('js_imports','js_imports/admin/admin_tables.htm');
		$this->f3->set('view','admin/users.htm');
	}

	private function check_password($pw, $confirm)
	{
		if(strlen($pw) < $this->f3->get("min_pw_length")) {
			return $this->f3->get('i18n_password_too_short');
		}
		else if($pw != $confirm) {
			return $this->f3->get('i18n_user_wrong_confirm');
		}
		else {
			return "";
		}
	}
	
	public function create_user() 
	{
		$id = $this->f3->get('PARAMS.id'); 
		if($this->f3->VERB==='POST')
		{
			$users = new User($this->db, $this->f3->get("table_prefix"));
			$pw = $this->f3->get('POST.password');
			if(strlen($pw)===0)
			{
				$this->f3->set('POST.password',$this->f3->get('POST.pw'));
			}
			else
			{
				$pwcheck = $this->check_password( $pw , $this->f3->get('POST.confirm'));
				if (strlen($pwcheck) > 0) {
					$this->f3->set('message', $pwcheck);
				}
				else
				{
					$password = password_hash($this->f3->get('POST.password'), PASSWORD_BCRYPT);
					$this->f3->set('message', "Password changed");
					$this->f3->set('POST.password', $password);
				}
			}
			if(null===$this->f3->get('POST.username')){
				$this->f3->set('POST.username',$this->f3->get('POST.email'));
			}
			$result=$users->add($this->f3->get('POST'));
			if($result==10 || $result==11)
			{
				$this->f3->set('message', "Could not create user, email already taken");
				$this->f3->set('view','admin/userdetails.htm');
			}
			else{
				$this->f3->reroute('/'.$this->f3->get("adminpage").'/users');
			}
		}
		$this->f3->set('view','admin/userdetails.htm');
	}
	
	public function show_user() 
	{
		$id = $this->f3->get('PARAMS.id'); 
		if($this->f3->VERB==="POST")
		{
			$users = new User($this->db, $this->f3->get("table_prefix"));
			$pw = $this->f3->get('POST.password');
			if(strlen(trim($pw))===0) {
				$this->f3->clear('POST.password');
			}
			else
			{
				$pwcheck = $this->check_password( $pw , $this->f3->get('POST.confirm'));
				if (strlen($pwcheck) > 0) {
					$this->f3->set('message', $pwcheck);
				}
				else
				{
					$password = password_hash($this->f3->get('POST.password'), PASSWORD_BCRYPT);
					$this->f3->set('message', "Password changed");
					$this->f3->set('POST.password', $password);
				}
			}
			$users->edit($id, $this->f3->POST);
		}
		else
		{
			$users = new User($this->db, $this->f3->get("table_prefix"));
			$id = $this->f3->get('PARAMS.id'); 
			$users->getById($id);
			if($users->dry()) { 
				$this->f3->error(404);
			}
		}
		$this->f3->set('js_imports','js_imports/admin/admin_tables.htm');

		$this->f3->set('user',$users);
		$this->f3->set('view','admin/userdetails.htm');
	}
	
	/**
	* manage filled out contact forms
	*/
	public function contact() {
		$contact = new ContactForm($this->db, $this->f3->get("table_prefix"));
		$this->f3->set('contacts',$contact->all());
		
		$this->f3->set('view','admin/contact_list.htm');
	}
	
	public function delete_contact() {
		$contact = new ContactForm($this->db, $this->f3->get("table_prefix"));
		$id = $this->f3->get('PARAMS.id'); 
		$contact->delete($id);
		$this->f3->reroute('/'.$this->f3->get("adminpage")."/contact");
	}
	
	public function show_contact() {
		$contact = new ContactForm($this->db, $this->f3->get("table_prefix"));
		$id = $this->f3->get('PARAMS.id'); 
		$contact->getById($id);
		if($contact->dry()) {
			$this->f3->error(404);
		}
		else{
			$this->f3->set('contact',$contact);
			$this->f3->set('view','admin/contact_details.htm');
		}
	}
	
	/**
	* page management
	*/	
	public function show_pages()
	{
		$prefix=$this->f3->get("table_prefix");
		$page = new Page($this->db, $prefix);
		$this->f3->set('pages',$page->pages_with_parents($prefix));
		$this->f3->set('js_imports',"/js_imports/admin/admin_tables.htm");
		$this->f3->set('view','admin/pages.htm');
	}	
	
	public function new_page()
	{
		$prefix=$this->f3->get("table_prefix");

		if($this->f3->VERB=="POST")
		{
			$page = new Page($this->db, $prefix);
			$page->all();
			
			//check if alias is unique
			$page->load(array('alias=?',$this->f3->get('POST.alias')));
			if($page->dry()){
				$this->f3->set('POST.site', $this->f3->get('site'));
				$page->add($this->f3->get('POST'));
				$this->f3->reroute('/'.$this->f3->get("adminpage").'/pages');
			}
			else {
				$page->copyFrom($this->f3->get('POST'));
				$this->f3->set('page',$page);
				$this->f3->set('message','URL must be a unique value, a page with this URL already exists. Please choose another one.');
			}
		}
		$metatags=new \MetaTags($this->db, $prefix);
		$this->f3->set('metatags',$metatags->allLinked(0));
		$p=new Page($this->db, $prefix);
		$this->f3->set('pages',$p->pages());
		$this->f3->set('js_imports','js_imports/admin/page.htm');	
		$this->f3->set('view','admin/page.htm');
	}
	
	public function edit_page()
	{
		$page_id=intval($this->f3->get('PARAMS.id')); //get page by pagename		
		$prefix=$this->f3->get("table_prefix");

		$metatags=new \MetaTags($this->db, $prefix);
		$page = new Page($this->db, $prefix);
		if($this->f3->exists('POST.edit_page'))
		{
			if( !$this->f3->exists('POST.show_in_menu') 
				|| $this->f3->get('POST.show_in_menu')=="" ){
					 $this->f3->set('POST.show_in_menu',0);
			}
			$page = new Page($this->db, $prefix);
			$page->edit($this->f3->get('PARAMS.id'), $this->f3->get('POST'));
			$mc=new \MetatagsContent($this->db, $prefix);
			$mc->updatePageMetas($page_id, $this->f3->get("POST.metatags")); //even if empty->remove existing!
			
			$this->f3->reroute('/'.$this->f3->get("adminpage").'/page/'.$page_id);
		}

		$page->getById($page_id);
		if($page->dry()) { 
			$this->f3->error(404);
		}
		else
		{
			$this->f3->set('page',$page);
			$p=new Page($this->db, $prefix);
			$this->f3->set('metatags',$metatags->allLinked($page_id));
			$this->f3->set('pages',$p->pages());
			$this->f3->set('view','admin/page.htm');
			$this->f3->set('js_imports','js_imports/admin/page.htm');
		}
	}
	
	public function delete_page()
	{
		$prefix=$this->f3->get("table_prefix");

		$page = new Page($this->db, $prefix);
		if($page->delete($this->f3->get("PARAMS.id"))){
			$this->f3->reroute('/'.$this->f3->get("adminpage").'/pages');	
		}
		else{
			echo "ERROR: This page can not be deleted";die;
		}
	}	
	
	/**
	* chunks management
	*/
	public function new_chunk()
	{
		if($this->f3->VERB=="POST")
		{
			$chunk = new Chunk($this->db, $this->f3->get("table_prefix"));
			$chunk->add($this->f3->get('POST'));
			$this->f3->reroute('/'.$this->f3->get("adminpage").'/chunks');
		}
		$this->f3->set('view','admin/show_chunk.htm'); 
	}
	
	public function chunks()
	{
		$chunk = new Chunk($this->db, $this->f3->get("table_prefix"));
		$this->f3->set('chunks',$chunk->all());
		$this->f3->set('view','admin/chunks.htm');
	}
	
	public function show_chunk()
	{
		$chunk = new Chunk($this->db, $this->f3->get("table_prefix"));
		if($this->f3->VERB=="POST")
		{
			$chunk->edit($this->f3->get('PARAMS.id'), $this->f3->get('POST'));
			$this->f3->reroute('/'.$this->f3->get("adminpage").'/chunks');
		}
		
		$chunk->getById($this->f3->get('PARAMS.id'));
		if($chunk->dry()) {
			$this->f3->error(404);
		}
		$page = new Page($this->db, $prefix);
		$this->f3->set('chunk',$chunk);
		$this->f3->set('pages',$page->findPagesUsingChunk($chunk->name, $prefix));
		$this->f3->set('view','admin/show_chunk.htm'); 
	}
	
	public function delete_chunk()
	{
		$chunk = new Chunk($this->db, $this->f3->get("table_prefix"));
		$chunk->delete($this->f3->get('PARAMS.id'));
		$this->f3->reroute('/'.$this->f3->get("adminpage").'/chunks');
	}
}