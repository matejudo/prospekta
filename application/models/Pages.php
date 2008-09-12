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
			$maxdepth = 100;
			while(!$done)
			{
				$stmt = $db->query("SELECT * FROM pros_page WHERE id = $result->parentid");
				$result = $stmt->fetchObject();
				$path = $result->slug . "/" . $path;
				if($result->parentid === NULL) $done = 1;
				$maxdepth--;
				if($maxdepth == 0)
				{
					$path .= "ERROR";
					break;
				}
			}
		}
		return $path;
	}
	
	public function getPathById($id)
	{
		$db = Zend_Registry::get("db");
		
		$stmt = $db->query("SELECT * FROM pros_page WHERE id = $id");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		$result = $stmt->fetchObject();	
		if($result != NULL)
		{ 
			$path = $result->slug;		
			if(!($result->parentid === NULL))
			{
				$done = 0;
				$maxdepth = 100;
				while(!$done)
				{
					$stmt = $db->query("SELECT * FROM pros_page WHERE id = $result->parentid");
					$result = $stmt->fetchObject();
					if($result === NULL) break;
					if($result->parentid === NULL) $done = 1;
					$path = $result->slug . "/" . $path;
					$maxdepth--;
					if($maxdepth == 0)
					{
						$path .= "ERROR";
						break;
					}
				}
			}
			return $path;
		}
		return "";
	}
	
	public function getBreadcrumbs($id)
	{
		$db = Zend_Registry::get("db");		
		$stmt = $db->query("SELECT * FROM pros_page WHERE id = $id");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		$result = $stmt->fetchObject();		
		
		$return[0]["title"] = $result->title;
		$return[0]["path"] = $result->slug;
		
		if(!($result->parentid === NULL))
		{
			$done = 0;
			$i = 1;
			while(!$done)
			{
				$stmt = $db->query("SELECT * FROM pros_page WHERE id = $result->parentid");
				$result = $stmt->fetchObject();
				$return[$i]["title"] = $result->title;
				$return[$i]["path"] = $result->slug;
				if($result->parentid === NULL) $done = 1;
			}
			$i++;
		}
		return $return;
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
			$subarray = array("path" => $this->getPath($item->slug), "id" => $item->id, "slug" => $item->slug, "id" => $item->id);
			array_push($return, $subarray);
		}
		return $return;		
	}
	
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
		// TODO:
		$db = Zend_Registry::get("db");
		$stmt = $db->query("
			SELECT * FROM pros_page
		");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchObject();	
	}
	
	public function getById($id)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("SELECT * FROM pros_page WHERE id = $id");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchObject();		
	}
	
	public function increaseOrdering()
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("UPDATE pros_page SET ordering = ordering + 1");
		$stmt->execute();	
	}
	
	public function fixOrdering($parentid)
	{
		$db = Zend_Registry::get("db");
		if($parentid === NULL)
			$stmt = $db->query("SELECT * FROM pros_page WHERE parentid IS NULL ORDER BY ordering ASC, modified DESC");
		else
			$stmt = $db->query("SELECT * FROM pros_page WHERE parentid = $parentid ORDER BY ordering ASC, modified DESC");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		$result = $stmt->fetchAll();
		$counter = 1;
		foreach($result as $curitem)
		{
			$stmt = $db->query("
				UPDATE pros_page SET ordering = $counter WHERE id = $curitem->id
			");
			$stmt->execute();
			$counter++;
		}
	}
	
	public function delete($ids)
	{
		$db = Zend_Registry::get("db");
		foreach($ids as $id)
		{
			$stmt = $db->query("
				SELECT * FROM pros_page WHERE id = $id
			");
			$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
			$item = $stmt->fetchObject();
			
			$stmt = $db->query("
				SELECT * FROM pros_page WHERE parentid = $item->id
			");
			$children = $stmt->fetchAll();
			
			if(!($children == NULL))
			{
				return false;
			}
		}
		
		$count = 0;
		foreach($ids as $id)
		{
			$stmt = $db->query("DELETE FROM pros_page WHERE id = $id");
			$stmt->execute();
			$stmt = $db->query("UPDATE pros_menu SET type = 'link', target = '#' WHERE target = $id AND type = 'page'");
			$stmt->execute();
			
			$count++;
		}
		return $count;
		
	}
	
	public function setPublish($data, $value)
	{
		$db = Zend_Registry::get("db");
		
		$inlist = "(";		
		foreach($data as $id)
		{
			$inlist .= "$id, ";
		}
		$inlist = substr($inlist, 0, strlen($inlist)-2);
		$inlist .= ")";
		$stmt = $db->query("
			UPDATE pros_page p
				SET p.published = $value
				WHERE p.id in $inlist
		");	
	}
	
	public function slugExists($slug)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("SELECT * FROM pros_page WHERE slug = '$slug'");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		$result = $stmt->fetchObject();
		if($result == NULL)
			return false;
		return true;
	}
	
	
}
