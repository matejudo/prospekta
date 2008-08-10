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
				ORDER BY article.order ASC, article.id ASC
		");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchAll();	
	}
	
	public function moveup($id)
	{
		$db = Zend_Registry::get("db");
		
		$order = $db->fetchOne("SELECT p.order FROM pros_article p WHERE id = $id");
		$neworder = $order-1;
		
		$stmt = $db->query("
			UPDATE pros_article p
				SET p.order = $order
				WHERE p.order = $neworder
		");				
		$stmt->execute();
		
		$stmt = $db->query("
			UPDATE pros_article p
				SET p.order = $neworder
				WHERE id = $id
		");				
		$stmt->execute();
	}
	
	public function movedown($id)
	{
		$db = Zend_Registry::get("db");
		
		$order = $db->fetchOne("SELECT p.order FROM pros_article p WHERE id = $id");
		$neworder = $order+1;
		
		$stmt = $db->query("
			UPDATE pros_article p
				SET p.order = $order
				WHERE p.order = $neworder
		");				
		$stmt->execute();
		
		$stmt = $db->query("
			UPDATE pros_article p
				SET p.order = $neworder
				WHERE id = $id
		");				
		$stmt->execute();
	}
	
}