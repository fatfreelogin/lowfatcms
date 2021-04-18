<?php

class Page extends _Model
{
/*
not allowed fields: 
	id
	 protected_from_deletion 
*/
	protected $allowed_fields = array(
		"pagetitle",
		"longtitle",
		"type",
		"description",
		"thumbnail",
		"alias",
		"published",
		"parent",
		"isfolder",
		"introtext",
		"content",
		"menuindex",
		"menutitle",
		"hidemenu",
		"searchable",
		"show_moreinfo",
		"showchildrenaslist",
		"canonical",
		"canonical_fr",
		"canonical_en",
		"canonical_nl",
		"canonical_be");
		
	public function __construct(DB\SQL $db, $tableprefix){
		parent::__construct($db, 'site_content', $tableprefix);
	}

	public function all() 
	{
		$this->load('',array('order'=>'menuindex ASC'));
		return $this->query;
	}
	
	public function allpublished()
	{
		$this->load('published=1',array('order'=>'menuindex ASC'));
		return $this->query;
	}
	
	/**
	* sitemap.xml
	*/
	public function sitemappages()
	{
		$this->load('published=1 AND type="document"',array('order'=>'menuindex ASC'));
		return $this->query;
	}
	
	/**
	* used in Controller beforeroute 
	* get menulinks
	*/
	public function getMenu()
	{
		$this->load('published=1 AND hidemenu=0',array('order'=>'menuindex ASC'));
		return $this->query;
	}
	
	public function getChildren($id) {
		$this->load(array('parent=? AND published=1 AND hidemenu=0',$id),array('order' => 'menuindex ASC, pagetitle ASC'));
		return $this->query;
	}
	
	public function getTagPages($id,$table_prefix) {
		$sql="SELECT c.alias, c.pagetitle, c.longtitle, c.thumbnail FROM ".$table_prefix."site_content c LEFT OUTER JOIN ".$table_prefix."metatags_content m on m.content_id=c.id WHERE c.published=1 AND m.metatag_id=:id";
		$out=$this->db->exec($sql, array(':id'=>$id));
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
	
	public function pages()
	{
		$result = $this->load();

		if($this->dry()){return;}
		$products=$result->find(array('order'=>'hidemenu DESC, menuindex ASC'));
		return $products;
	}
	
	public function pages_with_parents($table_prefix)
	{
		$sql="SELECT a.*, b.pagetitle as parent_title FROM ".$table_prefix."site_content a LEFT OUTER JOIN ".$table_prefix."site_content b ON a.parent = b.id ORDER BY parent, hidemenu DESC, menuindex ASC";
		$out=$this->db->exec($sql);
		return $out;
	}
	public function getCrumbs($id,$table_prefix)
	{
		$out=$this->db->exec("SELECT @r AS _id, ( SELECT @r := parent FROM ".$table_prefix."site_content  WHERE id = _id ) AS parent, ( SELECT alias FROM ".$table_prefix."site_content  WHERE id = _id ) AS parent_alias, (SELECT pagetitle FROM ".$table_prefix."site_content  WHERE id = _id ) AS parent_pagetitle, @l := @l + 1 AS level FROM ( SELECT @r := :id, @l := 0 ) vars, ".$table_prefix."site_content  h WHERE @r <> 0", array(':id'=>$id));
		return $out;
	}
	
	public function getByPagename($alias) {
		$this->load(array('alias=?',$alias));
		$this->copyTo('POST');
	}
	
	/**
	* find all pages using a chunk
	*/
	public function findPagesUsingChunk($chunkname, $table_prefix)
	{
		$chunkname2="%{{ ".$chunkname."%";
		$chunkname="%{{".$chunkname."%";
		
		$out=$this->db->exec("SELECT id, pagetitle, alias FROM ".$table_prefix."site_content WHERE content LIKE :chunkname OR content LIKE :chunkname2", array(':chunkname'=>$chunkname,':chunkname2'=>$chunkname2));

		return $out;
	}

	
	public function search($searchterm,$table_prefix) 
	{
		$out=null;
		if(strlen(trim($searchterm)) == 0){
			return $out;
		}
		if (strpos($searchterm, '0x') !== false){ //hex input, sql injection attempt?
			return $out;
		}
		if (strpos($searchterm, ' ') !== false)
		{
			$search=str_replace(" ","+", $searchterm);
			$sql="SELECT pagetitle, alias, longtitle, content, description, MATCH (`content`) AGAINST (:search IN BOOLEAN MODE) AS relevance1, MATCH (`pagetitle`) AGAINST (:search IN BOOLEAN MODE) AS relevance2 FROM ".$table_prefix."site_content WHERE published =1 AND searchable=1 AND type='document' HAVING (relevance1 + relevance2) > 0 ORDER BY relevance2 DESC, relevance1 DESC";
 			$out=$this->db->exec($sql, array(":search"=>"+".$search."*"));
		}
		else
		{
			$sql="SELECT pagetitle, alias, longtitle, content, description FROM ".$table_prefix."site_content WHERE published =1 AND type='document' AND searchable=1 AND (pagetitle like :searchterm OR content LIKE :searchterm)";
			$out=$this->db->exec($sql, array(":searchterm"=>'%'.$searchterm.'%'));
		}
		return $out;
	}

}
