<?php

require_once('Zend/Controller/Action.php');

class PageController extends Zend_Controller_Action
{

	function init()
	{
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl(); 
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
		$this->view->id = $this->_getParam("id");
		$pages = new Pages();
		$this->view->page = $pages->getById($this->view->id);
		$this->view->subelements = $pages->getChildrenById($this->view->id);
		foreach($this->view->subelements as $elem)
		{
			$elem->path = $pages->getPath($elem->slug);
		}
		$this->view->path = array_reverse($pages->getBreadcrumbs($this->view->id));
		
		$sidebars = new Sidebar();
		$this->view->leftsidebar = $sidebars->render($this->view->id, "Lijevo", $this->view->baseUrl);
		$this->view->rightsidebar = $sidebars->render($this->view->id, "Desno", $this->view->baseUrl);

	}

}