<?php

class Gallery extends AppModel {
    var $name = 'Gallery';
	var $useTable = 'dynamic';

	var $hasMany = array('Tags' => 
						array('className'  => 'Tag',
							  'foreignKey' => 'did',
							  'dependent'  => true,
							  'order'      => 'display'
						)
	               );
	
	function afterFind($result) {
		if (!isset($result[0]['Gallery'])) { return $result; }
		for($i = 0; $i < count($result); $i++) {
			$description = $result[$i]['Gallery']['description'];
			if (empty($description)) {
				$result[$i]['Gallery']['description_clean'] = __('This gallery does not have a description.', true);
			} else {
				$result[$i]['Gallery']['description_clean'] = $description;
			}
		}
		return $result;
	}
	
	////
	// callbacks to clear the cache
	////
	function afterSave() {
		$this->popCache();
		return true;
	}
	
	function beforeDelete() {
		$this->popCache();
		return true;
	}
	
	function popCache() {
		$id = $this->id;
		$targets = array("images_gid_{$id}", "images_gallery_{$id}");
		$this->clearCache($targets);
	}
	
	function isMain($id) {
		$this->id = $id;
		$gallery = $this->read();
		return $gallery['Gallery']['main'];
	}
	
	////
	// Reorder based on preset
	////
	function reorder($id) {
		// On really large galleries, this might take a while
		if (function_exists('set_time_limit')) {
			set_time_limit(0);
		}
		$this->id = $id;
		$this->recursive = 2;
		$gallery = $this->read();
		$order = $gallery['Gallery']['sort_type'];
		if ($order != 'manual') {
			$ids = array();
			switch($order) {
				case('album title');
					$albums = $gallery['Tags'];
					$names = array();
					foreach($albums as $a) {
						$names[] = $a['Album']['name'] . '__~~__' . $a['id'];
					}
					natcasesort($names);
					$names = array_values($names);
					for($i = 0; $i < count($names); $i++) {
						$bits = explode('__~~__', $names[$i]);
						$this->Tag->id = $bits[1];
						$this->Tag->saveField('display', $i+1);
					}
					break;
				default:
					if ($order == 'date (modified)') {
						$sql = '`Album`.modified DESC';
					} else {
						$sql = '`Album`.created';
						if ($order == 'date (newest first)') { $sql .= ' DESC'; }
					}
					$albums = $gallery['Tags'];
					$aids = array();
					foreach($albums as $a) {
						$aids[] = $a['Album']['id']; 
					}
					$aids = join(',', $aids);
					$conditions = "`Album`.id IN ($aids)";
					$new_albums = $this->Tag->findAll($conditions, null, $sql);
					$i = 1;
					foreach($new_albums as $album) {
						$this->Tag->updateAll(array('display' => $i), "did = $id AND aid = {$album['Album']['id']}");
						$i++;
					}
					break;
			}
		}
		return true;
	}
}

?>