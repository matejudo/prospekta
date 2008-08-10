<?php
class Users extends Zend_Db_Table
{
	protected $_name = 'pros_user';
	
	public function getUsers()
	{
		global $db;
		$stmt = $db->query("
			SELECT id, username, fullname
				FROM pros_user;
		");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchAll();	
	}
}