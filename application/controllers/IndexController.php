<?php

class IndexController extends Zend_Controller_Action
{
	function init()
	{
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
		$this->view->baseUrl(); 
	}
	
	function indexAction()
	{
		$this->_helper->layout->setLayout('frontpage'); 
		$this->view->title = "Dobrodošli";
		$news = new News();
		$this->view->news = $news->fetchAll();
		$najave = new Najave();
		$this->view->najave = $najave->fetchAll();
	}
}