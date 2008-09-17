<?php

require_once('Zend/Controller/Action.php');

class GalleryController extends Zend_Controller_Action
{

	function init()
	{
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl(); 
		$this->_helper->layout->setLayout('gallery'); 
		$menu = new Menu();
		
		$ret = $menu->render("Glavni", $this->view->baseUrl);
		$this->view->topmenu = $ret->topmenu;
		$this->view->submenu = $ret->submenu;
		$this->view->menucounter = $ret->counter;
		
		$this->view->leftmenu = $menu->render("Lijevi", $this->view->baseUrl);
	}
	
	public function indexAction()
	{
		$this->view->baseUrl();
		

		
	}

}