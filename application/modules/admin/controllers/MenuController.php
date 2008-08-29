<?php

require_once 'Zend/Controller/Action.php';

class Admin_MenuController extends Zend_Controller_Action
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
		$cat = $this->_getParam('name', 'Glavni');
		$this->view->category = $cat;
		$menu = new Menu();		
		$this->view->tree = $menu->getTree("Glavni");	
	}
	
	public function categoryAction()
	{
		$category = $this->_getParam('name', 'Glavni');
		$this->_setParam("category", $category);
		$this->_forward("index", "menu", "admin");
	}
	
	public function moveupAction()
	{
		$menu = new Menu();
		$id = $this->_getParam("id");
		$item = $menu->getById($id);
		$menu->fixOrdering($item->parentid);
		$menu->moveUp($id);
		$this->_redirect('admin/menu');
	}
	
	public function movedownAction()
	{
		$menu = new Menu();
		$id = $this->_getParam("id");
		$item = $menu->getById($id);
		$menu->fixOrdering($item->parentid);
		$menu->moveDown($id);
		$this->_redirect('admin/menu');
	}
	
	public function newAction()
	{
		$this->view->menu = new stdClass();
		$this->view->menu->title = "";
		$this->view->menu->description = "";
		$this->view->menu->menu = "";
		$this->view->menu->published = "1";
		$this->view->menu->id = "-1";
		$this->view->menu->parentid = "-1";
		$this->view->menu->ordering = "0";
		$this->view->menu->type = "page";
		$this->view->menu->target = "0";
		$pages = new Pages();
		$this->view->pages = $pages->getAllPaths();	
		$menu = new Menu();
		$this->view->menuitems = $menu->getAllPaths("Glavni");	
		$this->render("edit");
	}
	
	public function editAction()
	{
		$menu = new Menu();
		$id = $this->_getParam("id");
		$this->view->menu = $menu->getById($id);
		$this->view->paths = $menu->getAllPaths();	
		if($this->view->menu->parentid === NULL) $this->view->menu->parentid = "-1";
		$editor = new TextEditor();
		$this->view->editor = $editor->getHTML("text", $this->view->menu->text);
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
			    'title'      	=> $this->_request->getParam("title"),
			    'text'			=> $this->_request->getParam("text"),
				'ordering'		=> $this->_request->getParam("ordering"),
				'published'		=> $this->_request->getParam("published"),
				'modified'		=> $date->toString("YYYY-MM-dd HH:mm:ss"),
				'author'		=> Zend_Auth::getInstance()->getIdentity()->fullname
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
			
			if($this->_request->getParam("continue") == "1")
				$redirecturl = 'admin/menu/edit/id/' . $this->_request->getParam("id");
			else
				$redirecturl = '/admin/menu/';
		}

		$this->_redirect($redirecturl);
	}
	
	
	public function deleteAction()
	{
		if($this->getRequest()->isPost())
		{
			$this->view->deleted = 0;
			$menu = new menu();
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
	
	
	public function fixOrdering($parentid)
	{
		$db = Zend_Registry::get("db");
		if($parentid === NULL)
			$stmt = $db->query("SELECT * FROM pros_page WHERE parentid IS NULL ORDER BY ordering ASC, modified DESC");
		else
			$stmt = $db->query("SELECT * FROM pros_page WHERE parentid = $parentid ORDER BY ordering ASC, modified DESC");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		$result = $stmt->fetchAll();
		$counter = 1;
		foreach($result as $curitem)
		{
			$stmt = $db->query("
				UPDATE pros_page SET ordering = $counter WHERE id = $curitem->id
			");
			$stmt->execute();
			$counter++;
		}
	}
}
