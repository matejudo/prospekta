<?php
	
require_once 'Zend/Controller/Action.php';

class Admin_IndexController extends Zend_Controller_Action
{
	public function indexAction()
	{
		global $view;
		Zend_Debug::dump($view);
//		$view = new Zend_View::getInstance();
		//$view->baseUrl();
		$this->_helper->layout->setLayout('admin');
		$view->render("login.phtml");
		return;
		
			$struct = new Structure();
		$allitems = $struct->getAllChildren("prospekta");
		$this->view->allitems = array();
		foreach($allitems as $curitem)
		{
			array_push($this->view->allitems, $struct->getPath($curitem->slug));
		}
	}
}