<?php
	
require_once 'Zend/Controller/Action.php';

class Admin_IndexController extends Zend_Controller_Action
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
		$this->_helper->layout->setLayout('admin'); 
			$struct = new Structure();
		$allitems = $struct->getAllChildren("prospekta");
		$this->view->allitems = array();
		foreach($allitems as $curitem)
		{
			array_push($this->view->allitems, $struct->getPath($curitem->slug));
		}
	}
	

}