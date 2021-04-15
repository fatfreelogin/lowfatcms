<?php

class News extends _Model
{
	protected $allowed_fields = array(
		"spec",
		"title",
		"author",
		"intro_text",
		"full_article",
		"thumbnail",
		"published",
		"link");
		
	public function __construct(DB\SQL $db, $tableprefix) {
		parent::__construct($db, 'news', $tableprefix);
	}

	public function all($spec, $published=1) {
		if($published==1){
			$this->load(array("spec=? AND published =1", $spec));	
		}
		else{
			$this->load(array("spec=?", $spec));	
		}
		return $this->query;
	}
	
	public function latest($limit=3, $published=1) 
	{
		if($published==1){
			$this->load('published=1',array('order'=>'created_at DESC', 'limit' => $limit));	
		}
		else{
			$this->load('',array('order'=>'created_at DESC', 'limit' => $limit));
		}

		return $this->query;
	}
	
	public function add($unsanitizeddata) 
	{
		$data=$this->sanitizeInput($unsanitizeddata, $this->allowed_fields);
		$this->copyFrom($data);
		$this->save();
		return $this->get('_id');
	}
	
	public function edit($id,$unsanitizeddata) 
	{
		$data=$this->sanitizeInput($unsanitizeddata, $this->allowed_fields);
		$this->load(array('id=?',$id));
		$this->copyFrom($data); 
		$this->update();
	}
	
	public function getById($id) {
		$this->load(array('id=?',$id));
		return $this->query;
	}
	
	public function delete($id) 
	{
		$this->load(array('id=?',$id));
		$this->erase();
	}
}
