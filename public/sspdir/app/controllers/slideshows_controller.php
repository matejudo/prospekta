<?php

class SlideshowsController extends AppController
{
    var $name = 'Slideshows';
	var $uses = array('Slideshow');
	var $helpers = array('Html', 'Javascript', 'Ajax');
	var $non_ajax_actions = array('index', '_list');
	
	// Only logged in users should see this controller's actions
 	function beforeFilter() {
		// Protect ajax actions
		if (!in_array($this->action, $this->non_ajax_actions)) {
			$this->verifyAjax();
		}
		// Check session
		$this->checkSession();
	}
	
	////
	// Slideshows listing
	////
	function index() {
		$this->pageTitle = "Slideshows";
	}
	
	////
	// Create slideshow
	////
	function create() {
		if ($this->Slideshow->save($this->data)){
			$this->_list();
		}
	}
	
	////
	// Delete gallery
	////
	function delete() {
		if ($this->Slideshow->del($this->params['form']['id'])) {
			$this->_list();
		}
	}
	
	////
	// Private function to refresh list
	////
	function _list() {
		$this->Slideshow->cacheQueries = false;
		$this->set('shows', $this->Slideshow->findAll());
		$this->render('list', 'ajax');
	}
}

?>