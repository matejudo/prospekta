<?php
class Settings extends Zend_Db_Table
{
	protected $_name = 'pros_setting';

	public function get($key)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("SELECT * FROM pros_setting WHERE key = '$key'");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		$item = $stmt->fetchObject();
		return $item->value;
	}
	
	public function set($key, $value)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("SELECT * FROM pros_settings WHERE key = '$key'");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		$item = $stmt->fetchObject();
		if($item == NULL)
		{
			$stmt = $db->query("INSERT INTO pros_setting (key, value) VALUES ('$key', '$value'')");
			$stmt->execute;
		}
		else
		{
			$stmt = $db->query("UPDATE pros_setting SET key = '$key', value = '$value'");
			$stmt->execute;
		}
	}
}