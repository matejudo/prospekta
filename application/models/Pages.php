<?php
class Pages extends Zend_Db_Table
{
	protected $_name = 'pros_page';
	
	public function getChildren($parentSlug)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("
			SELECT * FROM pros_page p
				WHERE p.parentid = 
					(SELECT q.id FROM pros_structure q WHERE q.slug = '$parentSlug');
		");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchAll();	
	}
	
	public function getPath($slug)
	{
		$db = Zend_Registry::get("db");
		
		$stmt = $db->query("SELECT * FROM pros_page WHERE slug = '$slug'");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		$result = $stmt->fetchObject();		
		$path = $result->slug;
		
		if(!($result->parentid === NULL))
		{
			$done = 0;
			while(!$done)
			{
				$stmt = $db->query("SELECT * FROM pros_page WHERE id = $result->parentid");
				$result = $stmt->fetchObject();
				$path = $result->slug . "/" . $path;
				if($result->parentid === NULL) $done = 1;
			}
		}
		return $path;
	}
	

	
	public function getTree($id = NULL)
	{
		$db = Zend_Registry::get("db");
		if($id === NULL) 
			$stmt = $db->query("SELECT * FROM pros_page WHERE parentid IS NULL order by ordering");
		else
			$stmt = $db->query("SELECT * FROM pros_page WHERE parentid = $id  order by ordering");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		$result = $stmt->fetchAll();
		foreach($result as $item)
		{
			$item->depth = $this->getDepth($item->slug);
			$item->children = $this->getTree($item->id);
		}
		return $result;		
	}
	
	public function getFlatTree($id = NULL, $array = NULL)
	{
		if($array === NULL) $array = array();
		
		$db = Zend_Registry::get("db");
		if($id === NULL) 
			$stmt = $db->query("SELECT * FROM pros_page WHERE parentid IS NULL order by ordering");
		else
			$stmt = $db->query("SELECT * FROM pros_page WHERE parentid = $id  order by ordering");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		$result = $stmt->fetchAll();
		//Zend_Debug::dump($result);
		foreach($result as $item)
		{
			$item->depth = $this->getDepth($item->slug);	
			array_push($array, $item);
			$this->getFlatTree($item->id, &$array);
			
		}
		return $array;		
	}
	
	public function getAllPaths($id = NULL)
	{
		$return = array();
		$result = $this->getFlatTree();
		
		foreach($result as $item)
		{
			array_push($return, $this->getPath($item->slug));
		}
		return $return;		
	}
	
	
//	public function getPath($slug)
//	{
//		$return = "";
//		$db = Zend_Registry::get("db");
//		$stmt = $db->query("SELECT * FROM pros_page WHERE slug = '$slug' order by ordering");
//		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
//		$result = $stmt->fetchObject();
//		$return = " &raquo; " . $result->slug;
//		while(!($result->parentid === NULL))
//		{
//			$stmt = $db->query("SELECT * FROM pros_page WHERE id = '$result->parentid'");
//			$result = $stmt->fetchObject();
//			$return = " &raquo; " . $result->slug . $return;
//		}
//		
//		return $return;
//	}
	
	
	
	public function moveUp($id)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("SELECT * FROM pros_page WHERE id = $id");
		$result = $stmt->fetchObject();
		
		Zend_Debug::dump($result);
		
		if($result->parentid === NULL)
		{		
			$stmt = $db->query("UPDATE pros_page SET ordering = ordering + 1 WHERE parentid IS NULL AND ordering = $result->ordering - 1");
			$stmt->execute();
		}
		else
		{
			$stmt = $db->query("UPDATE pros_page SET ordering = ordering + 1 WHERE parentid = $result->parentid AND ordering = $result->ordering - 1");
			$stmt->execute();
		}
		$stmt = $db->query("UPDATE pros_page SET ordering = $result->ordering - 1 WHERE id = $id");
		$stmt->execute();

	}
	
	public function moveDown($id)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("SELECT * FROM pros_page WHERE id = $id");
		$result = $stmt->fetchObject();
		
		if($result->parentid === NULL)
		{		
			$stmt = $db->query("UPDATE pros_page SET ordering = ordering - 1 WHERE parentid IS NULL AND ordering = $result->ordering + 1");
			$stmt->execute();
		}
		else
		{
			$stmt = $db->query("UPDATE pros_page SET ordering = ordering - 1 WHERE parentid = $result->parentid AND ordering = $result->ordering + 1");
			$stmt->execute();
		}
		
		$stmt = $db->query("UPDATE pros_page SET ordering = $result->ordering + 1 WHERE id = $id");
		$stmt->execute();

	}
	
	
	public function setDepths($result)
	{
		$array = array();
		foreach($result as $item)
		{
			$item["depth"] = $this->getDepth($item["slug"]);
			array_push($array, $item);
		}
		return $array;
	}
	
	public function getDepth($slug)
	{
		$path = $this->getPath($slug);
		$pieces = explode("/", $path);
		return count($pieces);
	}
	
	public function getAllChildren($slug)
	{
		// TODO:
		$db = Zend_Registry::get("db");
		$stmt = $db->query("
			SELECT * FROM pros_page
		");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchAll();
	}
	
	
	public function getDepths()
	{
		// TODO:
		$db = Zend_Registry::get("db");
		$stmt = $db->query("
			SELECT * FROM pros_page
		");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchAll();
	}
	
	public function getLevel($level)
	{
		// TODO:
		$db = Zend_Registry::get("db");
		$stmt = $db->query("
			SELECT * FROM pros_page
		");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchAll();	
	}
	
	public function getPage($slug)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("
			SELECT * FROM pros_page
		");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchObject();	
	}
	
}