<?php
class Sidebar extends Zend_Db_Table
{
	/*
	 * Specijalne stranice:
	 * 		-1: Clanci Novosti
	 * 		-2: Clanci Zanimljivosti 
	 */

	protected $_name = 'pros_sidebar';
	
	public function get($pageid, $position = "Desno")
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("SELECT s.* FROM pros_sidebar s, pros_sidebar_page p WHERE s.id = p.sidebar_id AND p.page_id = $pageid AND s.position = '$position' AND s.published = 1 ORDER BY s.ordering ASC");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchAll();
	}
	
	public function getById($id)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("SELECT * FROM pros_sidebar WHERE id = $id ORDER BY ordering");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchObject();
	}
	
	public function countPages($sidebar)
	{
		$db = Zend_Registry::get("db");
		return $db->fetchOne('SELECT COUNT(*) FROM pros_sidebar WHERE sidebar_id = $sidebar');
	}
	
	public function fetchAllCount()
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("SELECT s.*, COUNT(p.sidebar_id) AS count FROM pros_sidebar s LEFT OUTER JOIN pros_sidebar_page p ON (s.id = p.sidebar_id) GROUP BY s.id ORDER BY ordering ");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchAll();
	}
	
	public function setPages($sidebar, $pages)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("DELETE FROM pros_sidebar_page WHERE sidebar_id = $sidebar");
		$counter = 0;
		foreach($pages as $page)
		{
			$stmt = $db->query("INSERT INTO pros_sidebar_page VALUES ($sidebar, $page)");
			$counter++;
		}
		return $counter;
	}
	
	public function getPages($sidebar)
	{
		$db = Zend_Registry::get("db");
		$result = array();
		$stmt = $db->query("SELECT * FROM pros_sidebar_page WHERE sidebar_id = $sidebar");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		$pages = $stmt->fetchAll();
		foreach($pages as $page)
		{
			array_push($result, $page->page_id);
		}
		return $result;
	}
	
	public function moveup($id)
	{
		$db = Zend_Registry::get("db");
		
		$this->fixOrdering();
		
		$order = $db->fetchOne("SELECT p.ordering FROM pros_sidebar p WHERE id = $id");
		$neworder = $order-1;
		
		$stmt = $db->query("
			UPDATE pros_sidebar p
				SET p.ordering = $order
				WHERE p.ordering = $neworder
		");				
		$stmt->execute();
		
		$stmt = $db->query("
			UPDATE pros_sidebar p
				SET p.ordering = $neworder
				WHERE id = $id
		");				
		$stmt->execute();
	}
	
	public function movedown($id)
	{
		$db = Zend_Registry::get("db");
		
		$this->fixOrdering();
		
		$order = $db->fetchOne("SELECT p.ordering FROM pros_sidebar p WHERE id = $id");
		$neworder = $order+1;
		
		$stmt = $db->query("
			UPDATE pros_sidebar p
				SET p.ordering = $order
				WHERE p.ordering = $neworder
		");				
		$stmt->execute();
		
		$stmt = $db->query("
			UPDATE pros_sidebar p
				SET p.ordering = $neworder
				WHERE id = $id
		");				
		$stmt->execute();
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
			UPDATE pros_sidebar p
				SET p.published = $value
				WHERE p.id in $inlist
		");	
	}
	
	public function delete($data)
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
			DELETE FROM pros_sidebar
				WHERE id IN $inlist;
		");	
	}
	
	public function fixOrdering()
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("SELECT * FROM pros_sidebar ORDER BY ordering ASC");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		$result = $stmt->fetchAll();
		$counter = 1;
		foreach($result as $curitem)
		{
			$stmt = $db->query("
				UPDATE pros_sidebar SET ordering = $counter WHERE id = $curitem->id
			");
			$stmt->execute();
			$counter++;
		}
	}
	
}