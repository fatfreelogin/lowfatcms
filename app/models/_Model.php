<?php

class _Model extends DB\SQL\Mapper 
{	
	public function __construct(DB\SQL $db, $table, $prefix=""){
		$table=$prefix . $table;
		parent::__construct($db, $table);
	}
	
	public function sanitizeInput(array $data, array $fieldNames) {
	   return array_intersect_key($data, array_flip($fieldNames));
	}
	
	public function getJsonData($forbidden=array())
	{
		$var = get_object_vars($this);
		$json_array=array();
		foreach ($var['fields'] as $key=>$value) {
			if(!in_array($key, $forbidden) ){

				if (is_object($value) && method_exists($value,'getJsonData')) {
					$value = $value->getJsonData();
				}
		
				$json_array[$key] = $value['value'];
			}
		}
		return $json_array;
	}

}