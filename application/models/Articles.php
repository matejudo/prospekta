<?php
class Articles extends Zend_Db_Table
{
	protected $_name = 'pros_article';
	
	public function fetchCategory($category)
	{
		$db = Zend_Registry::get("db");
		
		$stmt = $db->query("
			SELECT article.*, user.fullname as fullname
				FROM pros_article article, pros_user user
				WHERE article.category = '$category'
					AND user.id = article.author
				ORDER BY article.ordering ASC, article.id ASC
		");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchAll();	
	}
	
	public function moveup($id)
	{
		$db = Zend_Registry::get("db");
		
		$order = $db->fetchOne("SELECT p.ordering FROM pros_article p WHERE id = $id");
		$neworder = $order-1;
		
		$stmt = $db->query("
			UPDATE pros_article p
				SET p.ordering = $order
				WHERE p.ordering = $neworder
		");				
		$stmt->execute();
		
		$stmt = $db->query("
			UPDATE pros_article p
				SET p.ordering = $neworder
				WHERE id = $id
		");				
		$stmt->execute();
	}
	
	public function movedown($id)
	{
		$db = Zend_Registry::get("db");
		
		$order = $db->fetchOne("SELECT p.ordering FROM pros_article p WHERE id = $id");
		$neworder = $order+1;
		
		$stmt = $db->query("
			UPDATE pros_article p
				SET p.ordering = $order
				WHERE p.ordering = $neworder
		");				
		$stmt->execute();
		
		$stmt = $db->query("
			UPDATE pros_article p
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
			UPDATE pros_article p
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
			DELETE FROM pros_article
				WHERE id IN $inlist;
		");	
	}
	
	public function getBySlug($slug, $format = 0)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("SELECT * FROM pros_article WHERE slug = '$slug' AND published = 1");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		$item = $stmt->fetchObject();
		if($item == NULL)
		{
			$item = new stdClass();
			$item->title = "Traženi članak ne postoji";
			$item->text = "";
			$item->error = 1;
			$item->slug = "";
			return $item;
		}
		if($format)
		{
			$item->text = str_replace("<!-- pagebreak --></p>", "</p><!-- pagebreak -->", $item->text);
			$item->text = '<div class="intro">' . str_replace("<!-- pagebreak -->", "</div>", $item->text);
			$end = strpos($item->text, "<!-- pagebreak -->");	
		}
		return $item;	
	}
	
	public function getById($id, $format = 0)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("SELECT * FROM pros_article WHERE id = $id");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		$item = $stmt->fetchObject();
		if($format)
		{
			$item->text = str_replace("<!-- pagebreak --></p>", "</p><!-- pagebreak -->", $item->text);
			$item->text = '<div class="intro">' . str_replace("<!-- pagebreak -->", "</div>", $item->text);
			$end = strpos($item->text, "<!-- pagebreak -->");	
		}
		return $item;
	}
	
	public function getCategories()
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("SELECT category, COUNT(category) AS count FROM pros_article GROUP BY category");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchAll();
	}
	

	
	public function getArticles($category, $count = 1, $fulltext = 0)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("SELECT * FROM pros_article WHERE category = '$category' AND published = 1 ORDER BY ordering LIMIT 0,$count");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		$return = $stmt->fetchAll();
		foreach($return as $item)
		{
			$item->text = str_replace("<!-- pagebreak --></p>", "</p><!-- pagebreak -->", $item->text);
		
			
			if(!$fulltext)
			{
				$end = strpos($item->text, "<!-- pagebreak -->");
				if($end > 0)
				{
					$item->text = substr($item->text, 0, $end); 
					$item->more = true;
				}
				else
					$item->more = false;
			}
			else
			{
				$item->more = false;
			}
		}
		return $return;
	}
	
	public function slugExists($slug)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("SELECT * FROM pros_article WHERE slug = '$slug'");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		$result = $stmt->fetchObject();
		if($result == NULL)
			return false;
		return true;
	}
}