<?php

class Tag extends AppModel {
    var $name = 'Tag';
	var $useTable = 'dynamic_links';

	var $belongsTo = array('Album' =>
                           array('className'  => 'Album',
                                 'foreignKey' => 'aid'
                           ),
						   'Gallery' =>
						   array('className'  => 'Gallery',
								 'foreignKey' => 'did',
								 'order'      => 'main DESC'
						   )
                     );

	function afterFind($result) {
		if (empty($result) || !isset($result[0]['Tags'])) { return $result; }
		$result[0]['Tags'] = $this->Album->afterFind($result[0]['Tags']);
		return $result;
	}
	
	////
	// callbacks to clear the cache
	////
	function afterSave($created) {
		if ($created) {
			$tag = $this->read(null, $this->id);
			$this->Gallery->reorder($tag['Gallery']['id']);
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
		if (is_array($id)) {
			$id = $id['id'];
		}
		$tag = $this->read(null, $id);
		$targets = array("images_gallery_{$tag['Tag']['did']}", "images_gid_{$tag['Tag']['did']}");
		$this->clearCache($targets);
		$this->Gallery->id = $tag['Tag']['did'];
		$this->Gallery->saveField('modified', date('Y-m-d H:i:s'));
	}
}

?>