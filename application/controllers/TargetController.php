<?php

require_once 'Zend/Controller/Action.php';

class TargetController extends Zend_Controller_Action
{
	function init()
	{
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl(); 
	}
	
	public function indexAction()
	{
	
		$this->view->baseUrl();


	}
		public function outputAction()
	{
		$this->view->baseUrl();
		$this->view->data = $this->_getParam("data");
}	}