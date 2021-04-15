<?php

class Chunk extends _Model
{
/*
	disallowed fields: 
	id
	protected_from_deletion 
*/
	protected $allowed_fields = array(
		"name",
		"description",
		"snippet");
		
	public function __construct(DB\SQL $db, $tableprefix){
		parent::__construct($db, 'site_htmlsnippets', $tableprefix);
	}

	public function add($unsanitizeddata) 
	{
		$data=$this->sanitizeInput($unsanitizeddata, $this->allowed_fields);
		$this->copyFrom($data);
	
		$this->save();
		return $this->get('_id');
	}
	
	public function all() 
	{
		$this->load();
		return $this->query;
	}
	
	public function allpublished()
	{
		$this->load('published=1',array('order'=>'menuindex ASC'));
		return $this->query;
	}
	
	public function edit($id,$unsanitizeddata) 
	{
		$data=$this->sanitizeInput($unsanitizeddata, $this->allowed_fields);
		$this->load(array('id=?',$id));
		$this->copyFrom($data);
		$this->updated_at=date('Y-m-d H:i:s');
		$this->update();
	}
	
	public function getById($id) 
	{
		$this->load(array('id=?',$id)); 
		return $this->query;
	}
	
	public function delete($id) 
	{
		$this->load(array('id=?',$id));
		return $this->erase();
	} 
	
	public function getByName($name) {
		if($name!==null){
			$this->load(array('name=?',$name));
			return $this->snippet;
		}
		return '';
	}

}
