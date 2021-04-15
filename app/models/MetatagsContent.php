<?php

class MetatagsContent extends _Model
{
/*
not allowed fields: 
	id
*/
	protected $allowed_fields = array(
		"metatag_id",
		"content_id");
	
	private $prefix;
	
	public function __construct(DB\SQL $db, $tableprefix){
		$this->prefix=$tableprefix;
		parent::__construct($db, 'metatags_content', $tableprefix);
	}
	
	public function updatePageMetas($page_id, $metas) 
	{
		$this->db->exec("DELETE FROM ".$this->prefix."metatags_content WHERE content_id=?", $page_id);
		if($metas!==null){
			foreach($metas as $meta){
				$this->reset();
				$this->metatag_id=intval($meta);
				$this->content_id=intval($page_id);
				$this->save();
			}
		}
	}
	public function delete($id) 
	{
		$this->load(array('id=?',$id));
		return $this->erase();
	} 
}