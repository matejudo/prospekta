<?php

class CatController extends Zend_Controller_Action
{
	function indexAction()
	{
		$this->_helper->layout->setLayout('layout'); 
		$this->view->title = "Category";
		$this->view->cats = $this->_getParam('cats', array());
		global $db;
		
		$slug = $this->_getParam('slug');
		if($slug == "")
		{
			$this->view->slug = $this->_getParam('topslug');
		}
		else
		{
			$this->view->slug = $slug;
		}
		
		$struct = new Structure();
		$this->view->struct = $struct->getAllChildren("prospekta");
		
		$frontController = Zend_Controller_Front::getInstance();
		$this->view->front = $frontController->getControllerDirectory();
		
	
	}
}