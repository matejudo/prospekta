<?php
class Menu extends Zend_Db_Table
{
	protected $_name = 'pros_menu';
	
	public function getChildren($parentSlug)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("
			SELECT * FROM pros_menu p
				WHERE p.parentid = 
					(SELECT q.id FROM pros_structure q WHERE q.slug = '$parentSlug');
		");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchAll();	
	}
	
	public function getPathById($id)
	{
		$db = Zend_Registry::get("db");
		
		$stmt = $db->query("SELECT * FROM pros_page WHERE id = '$id'");
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
	
	public function getTree($menu, $id = NULL)
	{
		$db = Zend_Registry::get("db");
		if($id === NULL) 
			$stmt = $db->query("SELECT * FROM pros_menu WHERE parentid IS NULL AND menu = '$menu' order by ordering");
		else
			$stmt = $db->query("SELECT * FROM pros_menu WHERE parentid = $id AND menu = '$menu' order by ordering");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		$result = $stmt->fetchAll();
		foreach($result as $item)
		{
			$item->depth = $this->getDepthById($item->id);
			$item->children = $this->getTree($menu, $item->id);
		}
		return $result;		
	}
	
	public function getFlatTree($menu, $id = NULL, $array = NULL)
	{
		if($array === NULL) $array = array();
		
		$db = Zend_Registry::get("db");
		if($id === NULL) 
			$stmt = $db->query("SELECT * FROM pros_menu WHERE parentid IS NULL AND menu = '$menu' order by ordering");
		else
			$stmt = $db->query("SELECT * FROM pros_menu WHERE parentid = $id AND menu = '$menu' order by ordering");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		$result = $stmt->fetchAll();
		//Zend_Debug::dump($result);
		foreach($result as $item)
		{
			$item->depth = $this->getDepthById($item->id);	
			array_push($array, $item);
			$this->getFlatTree($menu, $item->id, &$array);
			
		}
		return $array;		
	}
	
	public function getAllPaths($menu, $id = NULL)
	{
		$return = array();
		$result = $this->getFlatTree($menu);
		
		foreach($result as $item)
		{
			$subarray = array("id" => $item->id, "title" => $item->title, "depth" => $this->getDepthById($item->id));
			array_push($return, $subarray);
		}
		return $return;		
	}
	
	public function moveUp($id)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("SELECT * FROM pros_menu WHERE id = $id");
		$result = $stmt->fetchObject();
		
		Zend_Debug::dump($result);
		
		if($result->parentid === NULL)
		{		
			$stmt = $db->query("UPDATE pros_menu SET ordering = ordering + 1 WHERE parentid IS NULL AND ordering = $result->ordering - 1");
			$stmt->execute();
		}
		else
		{
			$stmt = $db->query("UPDATE pros_menu SET ordering = ordering + 1 WHERE parentid = $result->parentid AND ordering = $result->ordering - 1");
			$stmt->execute();
		}
		$stmt = $db->query("UPDATE pros_menu SET ordering = $result->ordering - 1 WHERE id = $id");
		$stmt->execute();
	}
	
	public function moveDown($id)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("SELECT * FROM pros_menu WHERE id = $id");
		$result = $stmt->fetchObject();
		
		if($result->parentid === NULL)
		{		
			$stmt = $db->query("UPDATE pros_menu SET ordering = ordering - 1 WHERE parentid IS NULL AND ordering = $result->ordering + 1");
			$stmt->execute();
		}
		else
		{
			$stmt = $db->query("UPDATE pros_menu SET ordering = ordering - 1 WHERE parentid = $result->parentid AND ordering = $result->ordering + 1");
			$stmt->execute();
		}
		
		$stmt = $db->query("UPDATE pros_menu SET ordering = $result->ordering + 1 WHERE id = $id");
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
	
	public function getDepthById($id)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("SELECT * FROM pros_menu");
		$result = $stmt->fetchAll();
		
		$count = 1;
		$array = array();
		foreach($result as $item)
		{
			$array[$item->id] = $item->parentid;
		}
		
		$current = $array[$id];
		
		while($current != NULL)
		{
			$current = $array[$current];
			$count++;
		}
		

		return $count;
	}
	
	public function getAllChildren($slug)
	{
		// TODO:
		$db = Zend_Registry::get("db");
		$stmt = $db->query("
			SELECT * FROM pros_menu
		");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchAll();
	}
	
	
	public function getDepths()
	{
		// TODO:
		$db = Zend_Registry::get("db");
		$stmt = $db->query("
			SELECT * FROM pros_menu
		");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchAll();
	}
	

	
	public function getById($id)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("SELECT * FROM pros_menu WHERE id = $id");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchObject();		
	}
	
	public function increaseOrdering()
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("UPDATE pros_menu SET ordering = ordering + 1");
		$stmt->execute();	
	}
	
	public function fixOrdering($parentid)
	{
		$db = Zend_Registry::get("db");
		if($parentid === NULL)
			$stmt = $db->query("SELECT * FROM pros_menu WHERE parentid IS NULL ORDER BY ordering ASC");
		else
			$stmt = $db->query("SELECT * FROM pros_menu WHERE parentid = $parentid ORDER BY ordering ASC");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		$result = $stmt->fetchAll();
		$counter = 1;
		foreach($result as $curitem)
		{
			$stmt = $db->query("
				UPDATE pros_menu SET ordering = $counter WHERE id = $curitem->id
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
				SELECT * FROM pros_menu WHERE id = $id
			");
			$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
			$item = $stmt->fetchObject();
			
			$stmt = $db->query("
				SELECT * FROM pros_menu WHERE parentid = $item->id
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
			$stmt = $db->query("
				DELETE FROM pros_menu WHERE id = $id
			");
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
			UPDATE pros_menu p
				SET p.published = $value
				WHERE p.id in $inlist
		");	
	}
	
	
	
	public function render($tree, $baseUrl)
	{
		if($tree == "Glavni")
		{
			$menuitems = $this->getTree("Glavni");
			$pages = new Pages();
			$this->view = new stdClass();
			$this->view->topmenu = "";
			$this->view->submenu = "";
			$counter = 1;
			foreach($menuitems as $item)
			{
				$counter++;
				if($item->type == "")
					$path = "#";
				if($item->type == "link")
					$path = $item->target;
				if($item->type == "page")
				{
					if($item->target == "-2")
						$path = "#";
					elseif($item->target == "-1")
						$path = $baseUrl;
					else
						$path = $baseUrl . "/" . $pages->getPathById($item->target);
				}
				$this->view->topmenu .= '<li class="topitem" id="menuitem' . $counter . '"><a href="' . $path . '"><strong>' . $item->title . '</strong></a></li>';
				
				$this->view->submenu .= '<ul class="submenu" id="subtab' . $counter . '" style="display: none">';
				foreach($item->children as $subitem)
				{
					if($subitem->type == "")
						$path = "#";
					if($subitem->type == "link")
						$path = $subitem->target;
					if($subitem->type == "page")
					{
						if($subitem->target == "-2")
							$path = "#";
						elseif($subitem->target == "-1")
							$path = $baseUrl;
						else
							$path = $baseUrl . "/" . $pages->getPathById($subitem->target);
					}
					$this->view->submenu .= '<li><a href="' . $path . '"><strong>' . $subitem->title . '</strong>' . (($subitem->description != "") ? (" - " . $subitem->description) : "") . '</a></li>';
				}
				$this->view->submenu .= '</ul>';			
			}
			$this->view->counter = $counter;
			return $this->view;
		}
		
		if($tree == "Lijevi")
		{
			$leftmenuitems = $this->getTree("Lijevi");
			$pages = new Pages();
			$this->view = new stdClass();
			$this->view->leftmenu = "";
			$counter = 1;
			foreach($leftmenuitems as $item)
			{
				$counter++;
				if($item->type == "")
					$path = "#";
				if($item->type == "link")
					$path = $item->target;
				if($item->type == "page")
				{
					if($item->target == "-2")
						$path = "#";
					elseif($item->target == "-1")
						$path = $baseUrl;
					else
						$path = $baseUrl . "/" . $pages->getPathById($item->target);
				}
				
				if($item->children)
				{
					$this->view->leftmenu .= '<li style="background: url(\''.$baseUrl . '/images/' . $item->description.'\') 0px 8px no-repeat;">';
					$this->view->leftmenu .= '<a style="padding-left: 20px;" href="#" onclick="$(\'#sidesubtab'.$counter.'\').showsubmenu(); return false;">' . $item->title . '</a>';
					$this->view->leftmenu .= '<ul class="sidesubmenu" id="sidesubtab'.$counter.'" style="display: none;">';			
						foreach($item->children as $subitem)
						{
							if($subitem->type == "")
								$path = "#";
							if($subitem->type == "link")
								$path = $subitem->target;
							if($subitem->type == "page")
							{
								if($subitem->target == "-2")
									$path = "#";
								elseif($subitem->target == "-1")
									$path = $baseUrl;
								else
									$path = $baseUrl . "/" . $pages->getPathById($subitem->target);
							}
							$this->view->leftmenu .= '<li><a href="' . $path . '">' . $subitem->title . '</a></li>';
						}
					$this->view->leftmenu .= '</ul>';
					$this->view->leftmenu .= '</li>';
				}
				else
				{
					$this->view->leftmenu .= '<li style="background: url(\'' . $baseUrl . '/images/' . $item->description.'\') 0px 8px no-repeat;">';
					$this->view->leftmenu .= '<a style="padding-left: 20px;" href="' . $path . '">' . $item->title . '</a></li>';
				}									
			}
			
			return $this->view->leftmenu;
		}
	}
	
	
}