<?php

class PageController extends Controller 
{
	public function homepage()
	{
		$prefix=$this->f3->get("table_prefix");
		$this->f3->set('view','page/homepage.htm');
		$page = new Page($this->db,$prefix);
		
		$pagename="index";
		$page->getByPagename($pagename);
		
		if (strpos($page->content, '[[children') !== false) {
			$page->content = $this->displayChildren($page);
		}
		$this->f3->set('page',$page);
		$this->f3->set('page_content',$this->parseChunks($page->content));
		$this->f3->set('activemenulink',$pagename);
		if(isset($this->f3->showhomepagenews) && $this->f3->showhomepagenews) {
			$news = new News($this->db,$prefix);
			$this->f3->set('news', $news->latest(1,"news"));
		}
	}
	
	/**
	* search.html
	*/
	public function search()
	{
		$prefix=$this->f3->get("table_prefix");
		if ($this->f3->VERB === "POST") //get searchresults
		{
			$page = new Page($this->db,$prefix);
			$this->f3->set('search_results',$page->search($this->f3->get("POST.search_for"),$prefix));			
		}
		$this->f3->set('view','page/search.htm');
	}
	
	/**
	* standard pages
	*/
	public function show()
	{
		$prefix=$this->f3->get("table_prefix");
		
		$page = new Page($this->db,$prefix);
		$pagename=$this->f3->get('PARAMS.pagename'); //get page by pagename
		$page->getByPagename($pagename);
		if($page->dry() || $page->published==0) { 
			$this->f3->error(404);
		}
		elseif($page->type=="reference"){
			$this->f3->reroute($page->content);
			die;
		}
		else
		{
			$page->content=$this->parseChunks($page->content);
			
			if (strpos($page->content, '[[children') !== false) {
				$page->content = $this->displayChildren($page);
			}
			if (strpos($page->content, '[[gallery') !== false) {
				$page->content = $this->showGallery($page->content);
			}
			if (strpos($page->content, '[[metatag') !== false) {
				$page->content = $this->metatag_pages($page->content);
			}
			if (strpos($page->content, '[[listpics') !== false) {
				$page->content = $this->listpics($page->content);
			}
			if (strpos($page->content, '[[prevnext') !== false) {
				$page->content = $this->prevnext($page);
			}
			if($page->parent!=0 && $page->hidemenu==0)
			{
				$crumbsarray=array();
				$crumb_array=array_reverse($page->getCrumbs($page->id,$prefix));
				$this->f3->set('activemenulink',$crumb_array[1]['parent_alias']);
				
				foreach($crumb_array as $crumbs){
					if($crumbs['parent']==$this->f3->get("product_pageid") ||
						$page->show_moreinfo==1) {
						$this->f3->set('moreinfo_btn',true);
					}
					if($crumbs['parent']==0){
						$crumbsarray[]='<a href="index.html">'.$this->f3->get("i18n_homelink").'</a>';}
					else{ 
						$crumbsarray[]='<a href="'.$crumbs['parent_alias'].'.html">'.$crumbs['parent_pagetitle'].'</a>';	
					}
				}
				$this->f3->set('breadcrumbs',implode(" / ",$crumbsarray));
			} 
			else{
				$this->f3->set('activemenulink',$pagename);	
			}
			$jsfile=$this->f3->get("site_root").'app/views/js_imports/'.$pagename.'.htm';
			if(file_exists($jsfile)){
				$this->f3->set('js_imports','js_imports/'.$pagename.'.htm');
			}
			$this->f3->set('page',$page); 
			$this->f3->set('view','page/show_page.htm');
		}
	}
	
	/**
	* replace placeholder with  html chunk from database
	*/
	private function parseChunks($content)
	{
		$out=preg_replace_callback(
			"/{{(.*?)}}/",
			function ($m){ 
				$prefix=$this->f3->get("table_prefix");
				$chunks = new Chunk($this->db, $prefix);
				return $chunks->getByName($m[1]); 
			},
			$content);
			return $out;
	}
	
	/**
	* add previous and next buttons to go to sibling pages
	*/
	private function prevnext($page)
	{
		$prevnext=new Page($this->db,$this->f3->get("table_prefix"));
		$result = $prevnext->prevnext($page->parent,$page->menuindex);
		if(count($result)===0) return $page->content;
		if(count($result)===1) {
			if($result[0]['menuindex'] > $page->menuindex) {
				$out="<div class=\"row justify-content-between\"><div class=\"col-12 text-right\"><a href=\"".$result[0]['alias']."\" class=\"btn btn-primary\">".$result[0]['pagetitle']." ></a></div></div>";
			}
			else {
				$out="<div class=\"row justify-content-between\"><div class=\"col-12 text-left\"><a href=\"".$result[0]['alias']."\" class=\"btn btn-primary\">< ".$result[0]['pagetitle']."</a></div></div>";
			}
		}
		else {
			$out="<div class=\"row justify-content-between\"><div class=\"col-6 text-left\"><a href=\"".$result[0]['alias']."\" class=\"btn btn-primary\">< ".$result[0]['pagetitle']."</a></div><div class=\"col-6 text-right\"><a href=\"".$result[1]['alias']."\" class=\"btn btn-primary\">".$result[1]['pagetitle']." ></a></div></div>";
		}
		return preg_replace("/\[\[prevnext\]\]/", $out, $page->content);
	}
	
	/**
	* output a list of the current page's children
	* if id=page_id not set, current page children
	* if aslist=true, returns an unordered list (ul - li), else cards
	*/
	private function displayChildren($page)
	{
		$id=$page->id;
		$list=false;
		if (strpos($page->content, '[[children?') !== false) {
			$options=explode("&",$this->get_string_between($page->content,"[[children?","]]"));
			foreach ($options as $option)
			{
				$opt=explode("=",$option);
				if( trim($opt[0])=="id") $id=intval(trim($opt[1]));
				elseif(trim($opt[0])=="list") {
					$list=trim($opt[1]);
					if($list==="true" || intval($list===1)) $list=true;
					else $list=false;
				}
			}
		}
		
		$children = new Page($this->db,$this->f3->get("table_prefix"));
		if($list){$out="<ul>";}
		else{$out="";}
		foreach($children->getChildren($id) as $child)
		{
			if($list){$out.="<li><a href='".$child->alias.".html'>".$child->pagetitle."</a><br>".$child->description."</li>";}
			else{$out.="<div class=\"col-12 col-xs-6 col-sm-4\"><div class=\"card text-center category_child\"><img src=\"".$child->thumbnail.".jpg\" alt=\"".$child->pagetitle."\" class=\"card-img-top\" > <div class=\"card-body text-center\"><a  class=\"stretched-link\" href='".$child->alias.".html'>".$child->longtitle."</a></div></div></div>";}
		}
		if($list){$out.="</ul>";}
		else{$out.="";} 
		
		return preg_replace("/\[\[children(.*?)\]\]/", $out, $page->content);
	}
	
	/**
	* returns a gallery of images
	* @param	string $content	pagecontent, snippet will be replaced 
	* @return	string	with html, replaced snippet 
	*/
	private function showGallery($content)
	{
		$out.=preg_replace_callback(
			"/\[\[gallery\?(.*?)\]\]/",
			function ($m)
			{
				$modalid="g".rand(100,999); //pseudorandomstring to allow multiple modals on page
				$f_out="<div class=\"row\" id=\"gallery\" data-toggle=\"modal\" data-target=\"#".$modalid."\">";
				$img_array=explode(",", trim($m[1]));
				$itemcount=count($img_array);
				$i=0;
				foreach($img_array as $imgurlcap)
				{
					$im=explode("=>", $imgurlcap);
					$imgcap = str_replace("\"","", $im[0]);
					$imgurl = str_replace("\"","", $im[1]);
					$mdimg.='<div class="carousel-item'. ($i==0?' active':'').'">
						  <img class="d-block w-100" src="'.$imgurl.'">
						</div>';
					$f_out.='<div class="col-lg-4 col-6 text-center"><img src="'.$imgurl.'" class="w-100 gallery-img" data-target="#crs'.$modalid.'" data-slide-to="'.$i++.'" alt="'.$imgcap.'"></a><br><span class="gallery-text">'.$imgcap.'</span></div>';
				}
				$modal='<div class="modal fade" id="'.$modalid.'" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body"><div id="crs'.$modalid.'" class="carousel slide" data-ride="carousel"><div class="carousel-inner">'.$mdimg.'</div><a class="carousel-control-prev" href="#crs'.$modalid.'" role="button" data-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="sr-only">Previous</span></a><a class="carousel-control-next" href="#crs'.$modalid.'" role="button" data-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="sr-only">Next</span></a></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button></div></div></div></div>';
				return $f_out."</div>".$modal;
			},
			str_replace("\r\n","",$content));
		return $out;
	}
	
	/**
	* lists all images in specified folder
	* @param	string $content	pagecontent, snippet will be replaced 
	* @param	string $folder	folder on server containing the images 
	* @return	string	with html, replaced snippet 
	*/
	private function listpics($content)
	{
		$out=preg_replace_callback(
			"/\[\[listpics\?(.*?)\]\]/",
			function ($m)
			{
				$modalid="m".rand(100,999); //pseudorandomstring to allow multiple modals on page
				$folder= trim($m[1]);
				$imgfiles = glob( getcwd() . $folder.'*.{jpg,JPG,jpeg,JPEG,png,PNG}',GLOB_BRACE);
				$f_out="<div class=\"row\" id=\"gallery\" data-toggle=\"modal\" data-target=\"#".$modalid."\">";
				$i=0;
				$mdimg='';
				foreach($imgfiles as $filename)
				{
					$imgfile = basename($filename);
					$imgname=pathinfo($imgfile, PATHINFO_FILENAME );
					$mdimg.='<div class="carousel-item'. ($i==0?' active':'').'">
						  <img class="d-block w-100" src="'.$folder .$imgfile.'">
						</div>';
					$f_out.="<div class=\"col-6 col-sm-4 col-md-3 col-lg-2 text-center\"><img src=\"". $folder .$imgfile."\" class=\"w-100 gallery-img\" data-target=\"#crs".$modalid."\" data-slide-to=\"".$i++."\" alt=\"".$imgname."\"></div>";
				}
				$modal='<div class="modal fade" id="'.$modalid.'" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body"><div id="crs'.$modalid.'" class="carousel slide" data-ride="carousel"><div class="carousel-inner">'.$mdimg.'</div><a class="carousel-control-prev" href="#crs'.$modalid.'" role="button" data-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="sr-only">Previous</span></a><a class="carousel-control-next" href="#crs'.$modalid.'" role="button" data-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="sr-only">Next</span></a></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button></div></div></div></div>';
				return $f_out."</div>".$modal;	
			},
			str_replace("\r\n","",$content));
			
		return $out;

	}
	
	private function get_string_between ($str,$from,$to) {
		$string = substr($str, strpos($str, $from) + strlen($from));
		if (strstr ($string,$to,TRUE) != FALSE) {
			$string = strstr ($string,$to,TRUE);
		}
		return $string;
	}
	
	/**
	* list all pages linked to a metatag
	* @param	string $content	pagecontent, snippet will be replaced 
	* @return	string	with html, replaced snippet 
	*/
	private function metatag_pages($content)
	{
		$id= $this->get_string_between($content,"[[metatag?id=", "]]");
		$prefix=$this->f3->get("table_prefix");
		$children = new Page($this->db,$prefix);

		$page = new \Page($this->db, $prefix);
		$out="";
		foreach($children->getTagPages($id, $prefix) as $child)
		{
			$out.="<div class=\"col-12 col-xs-6 col-sm-4\"><div class=\"card text-center category_child\">";
			if(null!==$child['thumbnail'] && strlen(trim($child['thumbnail']))>0 ){
				$out.="<img src=\"".$child['thumbnail']."\" alt=\"".$child['pagetitle']."\" class=\"card-img-top\" >";
			}
			$out.="<div class=\"card-body text-center\"><a  class=\"stretched-link\" href='".$child['alias'].".html'>".$child['longtitle']."</a></div></div></div>";
		}
		$out="<div class=\"row\">".$out."</div>"; 
		return preg_replace("/\[\[metatag\?(.*?)\]\]/", $out, $content);
	}
	
	public function contact()
	{
		if ($this->f3->VERB == "POST")
		{
			if( $this->antispam($this->f3->get('POST')) )
			{
				$this->f3->set('page_head',$this->f3->get("i18n_error_header"));
				$this->f3->set('message',$this->f3->get("i18n_spam"));
				$this->f3->set('view','page/message.htm');
			}
			else
			{	
				//add form to database
				$contactform = new ContactForm($this->db, $this->f3->get("table_prefix"));
				$contactform->add($this->f3->POST);
				$msg= "Name: " . $contactform->name  . " <br>\r\nEmail: ". $contactform->email ."<br>\r\n". " <br>\r\n<br>".$contactform->message;

				//send mail
				$mail = new Mail();
				$mail->send( //sender, recipient, subject, message
					$this->f3->get('from_email'), 
					$this->f3->get('info_email'),
					"Feedback Form " . $this->f3->get('site'),
					$msg);

				$this->f3->set('page_head',$this->f3->get("i18n_thanks"));
				$this->f3->set('message',$this->f3->get("i18n_thanks_msg"));
				$this->f3->set('view','page/message.htm');
			}
		}
		else
		{
			$prefix=$this->f3->get("table_prefix");
			$page = new Page($this->db,$prefix);
			$page->getByPagename("contact");			
			if($page->dry()) {
				$this->f3->set('longtitle',$this->f3->get("i18n_contactlink"));	
			}
			else{
				$this->f3->set('longtitle',$page->longtitle);	
				$this->f3->set('content',$page->content);	
			}
			$this->f3->set('activemenulink',"contact");
			$this->f3->set('js_imports',"js_imports/contact.htm");
			
			$this->f3->set('view','page/contact_page.htm');
		}
	}
	
	/**
	* page /news.html
	* list of all newsitems
	*/
	public function allnews()
	{
		$news = new News($this->db,$this->f3->get("table_prefix"));		
		$this->f3->set('activemenulink',strtolower($this->f3->get("i18n_news")));

		$page=new Page($this->db,$this->f3->get("table_prefix"));
		$page->alias='news';
		$page->canonical='news.html';
		$page->pagetitle='news';
		$page->description='news for '.$this->f3->get("company");
		$this->f3->set('page', $page);
		$this->f3->set('news', $news->latest($limit));
		$this->f3->set('view','page/allnews.htm');
	}
	
	/**
	* page /news/@id 
	* news details
	*/
	public function news()
	{
		$news = new News($this->db,$this->f3->get("table_prefix"));
		$newsitem=$news->getById($this->f3->get('PARAMS.id'));

		$this->f3->set('view','page/show_news.htm');
		$this->f3->set('activemenulink',strtolower($this->f3->get("i18n_news")));
		
		$canonicallink="news/".$this->f3->get('PARAMS.id');
		
		$page=new Page($this->db,$this->f3->get("table_prefix"));		
		$page->canonical=$canonicallink;
		$this->f3->set('page', $page);
		$this->f3->set('news', $newsitem['0']);
	}
	
}