<?php

class MetaTags extends _Model
{
/*
not allowed fields: 
	id
*/
	protected $allowed_fields = array(
		"metatag",
		"active");
	private $prefix;
	public function __construct(DB\SQL $db, $tableprefix){
		$this->prefix=$tableprefix;
		parent::__construct($db, 'metatags', $tableprefix);
	}

	public function all() 
	{
		$this->load('');
		return $this->query;
	}

	public function pages($id) 
	{
		$sql="SELECT c.id, c.alias, c.pagetitle, n.metatag as metatagname FROM `".$this->prefix."site_content` c left join ".$this->prefix."metatags_content m on m.content_id=c.id left join ".$this->prefix."metatags n on n.id=m.metatag_id where m.metatag_id=?";
		$out=$this->db->exec($sql, $id);
		return $out;
	}


	public function allLinked($page_id) 
	{
		$sql="SELECT m.id as id, m.metatag, c.metatag_id FROM ".$this->prefix."metatags m LEFT JOIN ".$this->prefix."metatags_content c ON m.id=c.metatag_id AND c.content_id = ? order by m.id";
		$out=$this->db->exec($sql, $page_id);
		return $out;
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
	
}
