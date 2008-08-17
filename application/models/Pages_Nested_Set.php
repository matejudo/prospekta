<?php
class Pages extends Zend_Db_Table
{
	protected $_name = 'pros_structure';
	
	public function getChildren($parentSlug)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("
			SELECT node.slug, (COUNT(parent.slug) - (sub_tree.depth + 1)) AS depth
			FROM pros_page AS node,
				pros_page AS parent,
				pros_page AS sub_parent,
				(
					SELECT node.slug, (COUNT(parent.slug) - 1) AS depth
					FROM pros_page AS node,
					pros_page AS parent
					WHERE node.lft BETWEEN parent.lft AND parent.rgt
					AND node.slug = '$parentSlug'
					GROUP BY node.slug
					ORDER BY node.lft
				)AS sub_tree
			WHERE node.lft BETWEEN parent.lft AND parent.rgt
				AND node.lft BETWEEN sub_parent.lft AND sub_parent.rgt
				AND sub_parent.slug = sub_tree.slug
			GROUP BY node.slug
			HAVING depth = 1
			ORDER BY node.lft;

		");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchAll();	
	}
	
	public function getPath($slug)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("
			SELECT parent.slug
				FROM pros_page AS node,
					pros_page AS parent
				WHERE node.lft BETWEEN parent.lft AND parent.rgt
					AND node.slug = '$slug'
				ORDER BY parent.lft;
		");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchAll();	
	}
	
	public function getAllChildren($slug)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("
			SELECT node.slug
				FROM pros_page AS node,
					pros_page AS parent
				WHERE node.lft BETWEEN parent.lft AND parent.rgt
					AND parent.slug = '$slug'
				ORDER BY node.lft;

		");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchAll();
	}
	
	
	public function getDepth($depth)
	{
		// TODO: Prilagoditi SQL upit
		$db = Zend_Registry::get("db");
		$stmt = $db->query("
			SELECT node.slug, (COUNT(parent.name) - (sub_tree.depth + 1)) AS depth
			FROM nested_category AS node,
				nested_category AS parent,
				nested_category AS sub_parent,
				(
					SELECT node.name, (COUNT(parent.name) - 1) AS depth
					FROM nested_category AS node,
					nested_category AS parent
					WHERE node.lft BETWEEN parent.lft AND parent.rgt
					AND node.name = 'PORTABLE ELECTRONICS'
					GROUP BY node.name
					ORDER BY node.lft
				)AS sub_tree
			WHERE node.lft BETWEEN parent.lft AND parent.rgt
				AND node.lft BETWEEN sub_parent.lft AND sub_parent.rgt
				AND sub_parent.name = sub_tree.name
			GROUP BY node.name
			ORDER BY node.lft;
		");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchAll();	
	}
	
	public function getDepths()
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("
			SELECT node.title, node.slug, (COUNT(parent.slug) - 1) AS depth
				FROM pros_page AS node,
				pros_page AS parent
				WHERE node.lft BETWEEN parent.lft AND parent.rgt
				GROUP BY node.slug
				ORDER BY node.lft;
		");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchAll();
	}
	
	public function getLevel($level)
	{
		// TODO: Prilagoditi SQL upit
		$db = Zend_Registry::get("db");
		$stmt = $db->query("
			SELECT node.name, (COUNT(parent.name) - (sub_tree.depth + 1)) AS depth
			FROM nested_category AS node,
				nested_category AS parent,
				nested_category AS sub_parent,
				(
					SELECT node.name, (COUNT(parent.name) - 1) AS depth
					FROM nested_category AS node,
					nested_category AS parent
					WHERE node.lft BETWEEN parent.lft AND parent.rgt
					AND node.name = 'PORTABLE ELECTRONICS'
					GROUP BY node.name
					ORDER BY node.lft
				)AS sub_tree
			WHERE node.lft BETWEEN parent.lft AND parent.rgt
				AND node.lft BETWEEN sub_parent.lft AND sub_parent.rgt
				AND sub_parent.name = sub_tree.name
			GROUP BY node.name
			ORDER BY node.lft;
		");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchAll();	
	}
	
	public function getPage($slug)
	{
		$db = Zend_Registry::get("db");
		$stmt = $db->query("
			SELECT *
				FROM pros_page
				WHERE slug = '$slug';
		");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchObject();	
	}
	
}