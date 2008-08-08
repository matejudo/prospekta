<?php
class Structure extends Zend_Db_Table
{
	protected $_name = 'pros_structure';
	//private $db;
	
	
	public function getChildren($parentSlug)
	{
		global $db;
		$stmt = $db->query("
			SELECT node.slug, (COUNT(parent.slug) - (sub_tree.depth + 1)) AS depth
			FROM pros_structure AS node,
				pros_structure AS parent,
				pros_structure AS sub_parent,
				(
					SELECT node.slug, (COUNT(parent.slug) - 1) AS depth
					FROM pros_structure AS node,
					pros_structure AS parent
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
		global $db;
		$stmt = $db->query("
			SELECT parent.slug
				FROM pros_structure AS node,
					pros_structure AS parent
				WHERE node.lft BETWEEN parent.lft AND parent.rgt
					AND node.slug = '$slug'
				ORDER BY parent.lft;

		");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		return $stmt->fetchAll();	
	}
	
	public function getAllChildren($slug)
	{
		global $db;
		$stmt = $db->query("
			SELECT node.slug
				FROM pros_structure AS node,
					pros_structure AS parent
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
	
	public function getLevel($level)
	{
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
}