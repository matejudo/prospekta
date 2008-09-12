<?php

////
// Needed for RSS parsing and shared cache clearing function
////
class AppModel extends Model {
	////
	// Clear cache
	////
	function clearCache($files) {
		umask(0);
		@chmod(XML_CACHE, 0777);
		foreach($files as $file) {
			$caches = $this->_regExpSearch("/^$file.*/", XML_CACHE);
			if (!empty($caches)) {
				foreach($caches as $cache) {
					@unlink(XML_CACHE . DS . $cache);
				}
			}
		}
	}
	
	function beforeSave() {
		if ($this->hasField('created_by') && empty($this->id) && defined('CUR_USER_ID')) { 
			$this->data[$this->name]['created_by'] = CUR_USER_ID;
        }
		if ($this->hasField('modified') && defined('CUR_USER_ID')) {
			$this->data[$this->name]['updated_by'] = CUR_USER_ID;
			return true;
		}
		return true;
	}
	
	function _regExpSearch($regExp, $dir, $regType='P', $case='') {
		$func = ( $regType == 'P' ) ? 'preg_match' : 'ereg' . $case;
		$open = opendir($dir);
		$files = array();
		while( ($file = readdir($open)) !== false ) {
			if ($func($regExp, $file) ) {
				$files[] = $file;
			}
		}
		return $files;
	}
}

?>