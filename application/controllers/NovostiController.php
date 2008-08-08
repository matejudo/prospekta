<?php

class NovostiController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {	
		$this->view->title = "Dobrodošli";
		$news = new News();
		$this->view->news = $news->fetchAll();		
		
		
		
	$structure = new Structure();
	$this->view->mainmenu = $structure->getChildren("prospekta");
	
		
	}
	
	public function viewAction() {	
		$this->view->title = "Dobrodošli";
		$news = new News();
		$slug = $this->getRequest()->getParam("slug");
		$select = $news->select()->where('slug = ?', $slug);
		$this->view->news = $news->fetchAll($select);	
	
	}

}
