<?php

require_once 'Zend/Controller/Action.php';

class Admin_SidebarController extends Zend_Controller_Action
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
		$sidebars = new Sidebar();
		$this->view->sidebars = $sidebars->fetchAll();
	}
	
	public function editAction()
	{
		$sidebars = new Sidebar();
		$id = $this->_getParam("id");
		$this->view->sidebar = $sidebars->getById($id);
		$editor = new TextEditor();		
		$this->view->editor = $editor->getHTML("text", $this->view->sidebar->text);
	}
}