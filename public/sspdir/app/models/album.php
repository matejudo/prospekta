<?php

class Album extends AppModel {
    var $name = 'Album';
	var $components = array('Director');

	var $hasMany = array('Image' =>
	              		array('className'  => 'Image',
	                      	  'foreignKey' => 'aid',
							  'dependent'  => true,
							  'order' 	   => 'seq, src'	
	                	),
						'Tags' => 
						array('className'  => 'Tag',
							  'foreignKey' => 'aid',
							  'dependent'  => true
						)
	               );
	
	function beforeFind($queryData) {
		if (is_array($queryData['conditions'])) {
			$queryData['conditions'][] = "Album.name <> ''";
		} else {
			if (!empty($queryData['conditions'])) {
				$queryData['conditions'] .= " AND ";
			}
			$queryData['conditions'] .= "Album.name <> ''";
		}
		return $queryData;
	}
	
	function afterFind($result) {
		if (!isset($result[0]['Album'])) { return $result; }
		for($i = 0; $i < count($result); $i++) {
			$description = $result[$i]['Album']['description'];
			if (empty($description)) {
				$result[$i]['Album']['description_clean'] = __('This album does not have a description.', true);
			} else {
				$result[$i]['Album']['description_clean'] = $description;
			}
		}
		return $result;
	}
	
	////
	// callbacks to clear the cache
	////
	function afterSave() {
		$tags = $this->Tag->findAll(aa('aid', $this->id));
		if (!empty($tags)) {
			foreach($tags as $tag) {
				$this->Tag->Gallery->reorder($tag['Gallery']['id']);
			}
		}
		$this->popCache();
		return true;
	}
	
	function beforeDelete() {
		$this->popCache();
		return true;
	}
	
	function popCache() {
		$id = $this->id;
		$album = $this->read();
		$targets = array("images_album_{$id}", "images_album_.*,{$id}_");
		if (!empty($album['Tags'])) {
			foreach ($album['Tags'] as $tag) {
				$targets[] = 'images_gid_' . $tag['did'];
				$targets[] = 'images_gallery_' . $tag['did'];
			}
		}
		$this->clearCache($targets);
	}
	
	////
	// Find all active albums
 	////
	function findActive($order = 'name') {
		$this->unbindModel(array('hasMany' => array('Image', 'Tags')));
		return $this->findAll("active = 1", null, $order);
	}
	
	////
	// Quickly return images in array
	////
	function returnImages($id) {
		$this->id = $id;
		$album = $this->read();
		return $album['Image'];
	}
	
	////
	// Reorder based on preset
	////
	function reorder($id) {
		// On really large albums, this might take a while
		if (function_exists('set_time_limit')) {
			set_time_limit(0);
		}
		$this->id = $id;
		$album = $this->read();
		$order = $album['Album']['sort_type'];
		if ($order != 'manual') {
			$this->Image->coldSave = true;
			switch($order) {
				case('file name');
					$images = $this->Image->findAll(aa('aid', $id));
					$files = array();
					foreach($images as $i) {
						$files[] = $i['Image']['src'];
					}
					natcasesort($files);
					$files = array_values($files);
					for($i = 0; $i < count($files); $i++) {
						$temp = $this->Image->find(aa('src', $files[$i], 'aid', $id));
						$this->Image->id = $temp['Image']['id'];
						$this->Image->saveField('seq', $i+1);
					}
					break;
				default:
					$sql = '`Image`.created';
					if ($order == 'date (newest first)') { $sql .= ' DESC'; }
					$images = $this->Image->findAll(aa('aid', $id), null, $sql);
					for($i = 0; $i < count($images); $i++) {
						$this->Image->id = $images[$i]['Image']['id'];
						$this->Image->saveField('seq', $i+1);
					}
					break;
			}
			$this->Image->coldSave = false;
		}
		return true;
	}
}

?>