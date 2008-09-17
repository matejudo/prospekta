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
	
	public function render($pageid, $position = "Desno", $baseurl = "/")
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("SELECT s.* FROM pros_sidebar s, pros_sidebar_page p WHERE s.id = p.sidebar_id AND p.page_id = $pageid AND s.position = '$position' AND s.published = 1 ORDER BY s.ordering ASC");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		$all = $stmt->fetchAll();
		
		$return = "";
		
		foreach($all as $item)
		{
			switch($item->type)
			{
			case "Tekst":
				$return .= "<h3>" . $item->title . "</h3>" . $item->text;
				break;
			case "Anketa":				
				$stmt = $db->query("SELECT * FROM pros_poll WHERE id = $item->text");
				$poll = $stmt->fetchObject();
				$return .= "<h3>" . $item->title . "</h3>";
				$return .= "<p><strong>" . $poll->question . "</strong></p>";
				$stmt = $db->query("SELECT * FROM pros_poll_answer WHERE poll_id = $item->text ORDER BY id");
				$answers = $stmt->fetchAll();
				if(isset($_COOKIE['prospoll'.$poll->id]))
				{
					// Person has already voted
					$topcount = 0;
					foreach($answers as $answer)
					{
						if($answer->count > $topcount) $topcount = $answer->count;
					}
					$return .= "<p>";
					foreach($answers as $answer)
					{
						if($poll->count == 0) $poll->count = 1;
						if($topcount == 0) $topcount = 1;
						$perc = round(100 * $answer->count / $poll->count);
						$perc .= "%";
						$width = round(100 * $answer->count / $topcount);
						$width .= "%";
						$return .= $answer->answer . ' ' . $perc;
						$return .= '<span style="display: block; width: 100%; height:10px; border: 1px solid #cd4b0f;"><span style="width: ' . $width . '; background-color: #cd4b0f; height: 10px; display: block; margin-bottom: 5px;"></span></span>';
					}
					$return .= "</p>";
					
				}
				else
				{
					$return .= '<form name="prospoll'.$item->id.'" action="'.$baseurl.'/anketa/vote" method="post">';
					$return .= '<input type="hidden" name="pollid" value="' . $poll->id . '" />';
					$return .= '<input type="hidden" name="returnto" value="' . $_SERVER['REQUEST_URI'] . '" />';
					foreach($answers as $answer)
					{
						$return .= "<p>";
						if($poll->multiple)
							$return .= '<input type="checkbox" name="answer[]" id="answer' . $answer->id . '" value="' . $answer->id . '" /> ';
						else
							$return .= '<input type="radio" name="answer[]" id="answer' . $answer->id . '" value="' . $answer->id . '" /> ';
						$return .= '<label for="answer' . $answer->id . '">' . $answer->answer . '</label></p>';
					}
						$return .= '<input type="submit" value="Pošalji &raquo" />';
						$return .= '</form>';
				}
				break;
			default:
				$return .= "No content";
				break;
			}
		}
		return $return;
	}
	
	public function getById($id)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("SELECT * FROM pros_sidebar WHERE id = $id");
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
		$stmt = $db->query("
			DELETE FROM pros_sidebar_page
				WHERE sidebar_id IN $inlist;
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