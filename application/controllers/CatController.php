<?php

class CatController extends Zend_Controller_Action
{
	function init()
	{
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
	}

	function indexAction()
	{
		$this->view->baseUrl();
		$this->_helper->layout->setLayout('layout'); 

			$this->view->cats = $this->_getParam('cats', array());
		
		$where = "";
		$slug = $this->_getParam('slug');
		if($slug == "")
		{
			$this->view->slug = $this->_getParam('topslug');
			$where = $this->_getParam('topslug');
		}
		else
		{
			$this->view->slug = $slug;
			$where = $slug;
		}
		
		$pages = new Pages();		
		$this->view->page = $pages->getPage($where);
	
	
	}
}