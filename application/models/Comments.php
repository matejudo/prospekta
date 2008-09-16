<?php
class Comments extends Zend_Db_Table
{
	protected $_name = 'pros_comment';
	
	public function get($id)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("SELECT * FROM pros_comment WHERE article_id = $id ORDER BY created ASC");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchAll();
	}
	
	public function getById($id)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("SELECT * FROM pros_comment WHERE id = $id");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchObject();
	}
	
	public function delete($id)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("DELETE FROM pros_comment WHERE id = $id");
		$stmt->execute();
	}
}