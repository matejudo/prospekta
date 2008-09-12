<?php

require_once 'Zend/Controller/Action.php';

class Admin_MenuController extends Zend_Controller_Action
{

	protected $currentCategory = null;	

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
		
		$session = new Zend_Session_Namespace('Default');
		
		if (isset($session->menuCategory))
	    	$this->currentCategory = $session->menuCategory;
		else
		{
		    $session->menuCategory = "Glavni";
		    $this->currentCategory = $session->menuCategory;		    
		}
	}
	
	public function indexAction()
	{
		$this->view->baseUrl();
		$this->view->category = $this->currentCategory;
		$menu = new Menu();		
		$this->view->tree = $menu->getTree($this->view->category);	
	}
	
	public function categoryAction()
	{
		$category = $this->_getParam('name', 'Glavni');
		$session = new Zend_Session_Namespace('Default');
		$session->menuCategory = $category;
		$this->_redirect("admin/menu");
	}
	
	public function moveupAction()
	{
		$menu = new Menu();
		$id = $this->_getParam("id");
		$menu->fixOrdering($item->parentid);
		$menu->moveUp($id);
		$this->_redirect('admin/menu/');
	}
	
	public function movedownAction()
	{
		$menu = new Menu();
		$id = $this->_getParam("id");
		$menu->fixOrdering($item->parentid);
		$menu->moveDown($id);
		$this->_redirect('admin/menu/');
	}
	
	public function newAction()
	{
	// id 	parentid 	menu 	ordering 	title 	description 	type 	target 	published
		$this->view->menu = new stdClass();	
		$this->view->menu->id = "-1";
		$this->view->menu->parentid = "-1";
		$this->view->menu->menu = $this->currentCategory;
		$this->view->menu->ordering = "0";
		$this->view->menu->title = "";
		$this->view->menu->description = "";
		$this->view->menu->type = "page";
		$this->view->menu->target = "0";
		$this->view->menu->published = "1";
		$pages = new Pages();
		$this->view->pages = $pages->getAllPaths();	
		$menu = new Menu();
		$this->view->menuitems = $menu->getAllPaths($this->view->menu->menu);	
		$this->render("edit");
	}
	
	public function editAction()
	{
		$menu = new Menu();
		$id = $this->_getParam("id");
		$this->view->menu = $menu->getById($id);
		if($this->view->menu->parentid === NULL) $this->view->menu->parentid = "-1";
		$this->view->menuitems = $menu->getAllPaths($this->view->menu->menu);
		$pages = new Pages();
		$this->view->pages = $pages->getAllPaths();	
	}
	
	public function saveAction()
	{
		if($this->getRequest()->isPost())
		{		
			require_once 'Zend/Date.php';
			$date = new Zend_Date(Zend_Date::now(), Zend_Date::ISO_8601);			
			$menu = new Menu();
			$params = $this->_request->getParams();
			
			$data = array(
				'parentid'     	=> ($this->_request->getParam("parentid") == "-1") ? NULL : $this->_request->getParam("parentid"),
			    'menu'      	=> $this->_request->getParam("menu"),
				'title'			=> $this->_request->getParam("title"),
				'description'	=> ($this->_request->getParam("description") == "Opis") ? "" : $this->_request->getParam("description"),
				'type'			=> $this->_request->getParam("type"),
				'target'		=> $this->_request->getParam("target"),
				'published'		=> $this->_request->getParam("published")
			);			
		
			// New article -> SQL insert the data
			if($this->_request->getParam("id") == "-1")
			{	
				//$menu->increaseOrdering();
				$menu->insert($data); 
			}
			// Existing article -> SQL update the data
			else
			{
				$where = $menu->getAdapter()->quoteInto('id = ?', $this->_request->getParam("id"));			
				$menu->update($data, $where);
			}
			
			$menu->fixOrdering($data["parentid"]);
			$redirecturl = '/admin/menu/';
		}

		$this->_redirect($redirecturl);
	}
	
	
	public function deleteAction()
	{
		if($this->getRequest()->isPost())
		{
			$this->view->deleted = 0;
			$menu = new Menu();
			$data = array();
			$params = $this->_request->getParams();			
			foreach($params as $key => $value)
			{
				if($value == "1")
				{
					$id = substr($key, 2);
					array_push($data, $id);
				}
			}
			$this->view->deleted = $menu->delete($data);
		}
		if($this->view->deleted)
		{
			$this->_redirect("/admin/menu");
		}
		else
		{
			$this->view->baseUrl();
		}
	}

	public function publishAction()
	{

		$menus = new Menu();
		$data = array();
		$id = null;
		if($this->getRequest()->isPost())
		{
			
			$params = $this->_request->getParams();
			
			foreach($params as $key => $value)
			{
				if($value == "1")
				{
					$id = substr($key, 2);
					array_push($data, $id);
				}
			}			
		}
		else
		{	
			array_push( $data, $this->_getParam("id") );
		}		
		$menu->setPublish($data, 1);			
		$this->_redirect('admin/menu/');


	}
	
	public function unpublishAction()
	{
		$menu = new Menu();
		$data = array();
		if($this->getRequest()->isPost())
		{			
			$params = $this->_request->getParams();
			
			foreach($params as $key => $value)
			{
				if($value == "1")
				{
					$id = substr($key, 2);
					array_push($data, $id);
				}
			}
		}
		else
		{
			array_push( $data, $this->_request->getParam("id") );
		}
		$menu->setPublish($data, 0);
		$this->_redirect('admin/menu');
	}
	
	

}
