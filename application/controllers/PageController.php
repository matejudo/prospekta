<?php

require_once('Zend/Controller/Action.php');

class PageController extends Zend_Controller_Action
{

	function init()
	{
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl(); 
	}
	
	public function indexAction()
	{
		$this->view->baseUrl();
		$this->view->id = $this->_getParam("id");
		$pages = new Pages();
		$this->view->page = $pages->getById($this->view->id);
	}

}