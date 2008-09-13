<?php
class Polls extends Zend_Db_Table
{
	protected $_name = 'pros_poll';

	public function get($id)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("SELECT * FROM pros_poll WHERE id = $id");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchObject();
	}
	
	public function getAnswers($id)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("SELECT * FROM pros_poll_answer WHERE poll_id = $id ORDER BY id ASC");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchAll();
	}
	
	public function saveAnswers($id, $answers, $answerscount)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("DELETE FROM pros_poll_answer WHERE poll_id = $id");
		
		print_r($answers);

		for($i = 0; $i < count($answers); $i++)
		{
			if($answers[$i] == NULL) continue;
			if($answerscount[$i] == NULL) $answerscount[$i] = 0;
			$db->query("INSERT INTO pros_poll_answer VALUES (NULL, $id, '$answers[$i]', $answerscount[$i])");
		}

		return $i;
	}
}