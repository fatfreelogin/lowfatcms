<?php

class ContactForm extends _Model
{

	protected $allowed_fields = array(
		"name",
		"email",
		"subject",
		"message"
		);
		
	public function __construct(DB\SQL $db, $tableprefix) {
		parent::__construct($db,'contactform', $tableprefix);
	}

	public function all() {
		$this->load('',	array('order' => 'id DESC'));
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
		$this->updated_at=date('Y-m-d H:i:s');
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