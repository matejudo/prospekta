<?php

require_once 'Zend/Controller/Action.php';

class Admin_PageController extends Zend_Controller_Action
{

	function preDispatch()
	{
		$auth = Zend_Auth::getInstance();
		if (!$auth->hasIdentity()) {
			$this->_redirect('admin/auth/login');
		}
	}

	function init()
	{
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
		$this->_helper->layout->setLayout('admin'); 
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
	}
	
	public function indexAction()
	{
		$this->view->baseUrl();
		$pages = new Pages();		
		$this->view->tree = $pages->getTree();	
	}
	
	public function moveupAction()
	{
		$pages = new Pages();
		$id = $this->_getParam("id");
		$pages->moveUp($id);		
		$this->_redirect('admin/page');
	}
	
	public function movedownAction()
	{
		$pages = new Pages();
		$id = $this->_getParam("id");
		$pages->moveDown($id);		
		$this->_redirect('admin/page');
	}
	

	

}
