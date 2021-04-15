<?php

class User extends _Model
{

	protected $allowed_fields = array(
		"username",
		"password",
		"hash",
		"email",
		"user_type",
		"activated",
		"protected_from_deletion");
		
	public function __construct(DB\SQL $db, $tableprefix) {
		parent::__construct($db,'users', $tableprefix);
	}
	public function all() {
		$this->load();
		return $this->query;
	}
	
	public function add($unsanitizeddata) 
	{
		$data=$this->sanitizeInput($unsanitizeddata, $this->allowed_fields);
		$this->load(array('username=?',$data['username']));		
		if(!$this->dry()){
			return 10;
		}
		$this->load(array('email=?',$data['email']));
		if(!$this->dry()){
			return 11;
		}
		$this->copyFrom($data);
		$this->save();
		return 1;
	}
	
	public function edit($id,$unsanitizeddata) 
	{
		$data=$this->sanitizeInput($unsanitizeddata, $this->allowed_fields);
		$this->load(array('id=?',$id));
		$this->copyFrom($data);
		$this->update();
	}
	public function activate() 
	{
		$this->activated=1;
		$this->hash="";
		$this->update();
	}
	
	public function getById($id) {
		$this->load(array('id=?',$id));
		return $this->query;
	}
	
	public function delete($id) 
	{
		$this->load(array('id=?',$id));
		if($this->protected_from_deletion==1){
			return false;
		}
		else{ return $this->erase(); }
	}

	public function getByName($name) {
		$this->load(array('username=?', $name));
		if($this->dry()){
			$this->load(array('email=?', $name));
		}
	}
	
	public function getByEmail($email) {
		$this->load(array('email=?', $email));
		$this->copyTo('POST');
	}
	
	public function login($id) {
		$this->load(array('id=?',$id));
		$this->copyTo('SESSION');
	}
	
	public function getByHash($hash) {
		$this->load(array('hash=?',$hash));
	}
	
	public function setHash($hash)
	{
	//	$this->activated=0;
		$this->hash=$hash;
		$this->update();
	}
	
	public function getRegistration($code) {
		$this->load(array('registration=? AND user_type=10',$code));
	}
	
}
