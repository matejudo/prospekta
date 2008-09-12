<?php

class HelpController extends AppController {

	var $name = 'Help';
	var $uses = array();
	var $helpers = array('Html', 'Javascript', 'Ajax');
	
	function beforeFilter() {
		$this->checkSession();
	}
	
	function index() {
		
	}
}

?>