<?php

class Slideshow extends AppModel {
    var $name = 'Slideshow';
	
	////
	// Before save function ensures that the link is properly formed
	////
	function beforeSave() {
		$this->data['Slideshow']['url'] = 'http://' . str_replace('http://', '', $this->data['Slideshow']['url']);
		return true;
	}
	
	function afterSave() {
		cache(DIR_CACHE . DS . 'shows', null, '-1 day');
		return true;
	}
	
	function afterDelete() {
		$this->afterSave();
	}
}

?>