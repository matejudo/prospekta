<?php

require_once 'Zend/Controller/Action.php';

class ArticleController extends Zend_Controller_Action
{

	function init()
	{
		$this->view->baseUrl = $this->_request->getBaseUrl();
	}
	
	public function indexAction()
	{
		$this->view->baseUrl();
		
	}
}