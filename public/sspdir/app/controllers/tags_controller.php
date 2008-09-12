<?php

class TagsController extends AppController
{
    var $name = 'Tags';
	var $uses = array('Tag', 'Gallery', 'Album');
	
	// Only logged in users should see this controller's actions
 	function beforeFilter() {
		$this->checkSession();
		$this->verifyAjax();
	}
	
	////
	// Updates gallery albums order
	////
	function order() {
		$order = $this->params['form']['galleries-view'];
		while (list($key, $val) = each($order)) {
			$key++;
			$this->Tag->id = $val;
			$this->data['Tag']['display'] = $key;
			$this->Tag->save($this->data);
		}
	}
}