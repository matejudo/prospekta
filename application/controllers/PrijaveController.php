<?php

require_once 'Zend/Controller/Action.php';

class PrijaveController extends Zend_Controller_Action
{
	function init()
	{
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
	}
	
	function indexAction()
	{
		$this->view->baseUrl();
	}
}