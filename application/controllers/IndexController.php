<?php

class IndexController extends Zend_Controller_Action
{
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