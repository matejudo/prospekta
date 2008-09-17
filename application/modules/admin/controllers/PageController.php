<?php

require_once 'Zend/Controller/Action.php';

class Admin_PageController extends Zend_Controller_Action
{

	protected $_flashMessenger = null;

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
		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
	}
	
	public function indexAction()
	{
		$this->view->baseUrl();
		$pages = new Pages();		
		$this->view->tree = $pages->getTree();	
	}
	
	public function moveupAction()
	{
		$pages = new Pages();
		$id = $this->_getParam("id");
		$page = $pages->getById($id);
		$pages->fixOrdering($page->parentid);
		$pages->moveUp($id);
		$this->_redirect('admin/page');
	}
	
	public function movedownAction()
	{
		$pages = new Pages();
		$id = $this->_getParam("id");
		$page = $pages->getById($id);
		$pages->fixOrdering($page->parentid);
		$pages->moveDown($id);
		$this->_redirect('admin/page');
	}
	
	public function newAction()
	{
		$editor = new TextEditor();
		$this->view->editor = $editor->getHTML("text");
		$this->view->page = new stdClass();
		$this->view->page->title = "";
		$this->view->page->slug = "";
		$this->view->page->text = "";
		$this->view->page->published = "1";
		$this->view->page->id = "-1";
		$this->view->page->parentid = "-1";
		$this->view->page->ordering = "0";
		$this->view->page->showsub = "1";
		$pages = new Pages();
		$this->view->paths = $pages->getAllPaths();		
		$files = new Files();
		$this->view->files = $files->listFiles();
		$this->render("edit");
	}
	
	public function editAction()
	{
		$pages = new Pages();
		$id = $this->_getParam("id");
		$this->view->page = $pages->getById($id);
		$this->view->paths = $pages->getAllPaths();	
		if($this->view->page->parentid === NULL) $this->view->page->parentid = "-1";
		$editor = new TextEditor();
		$this->view->messages = $this->_flashMessenger->getMessages();
		$this->view->editor = $editor->getHTML("text", $this->view->page->text);
	}
	
	public function saveAction()
	{
		if($this->getRequest()->isPost())
		{		
			require_once 'Zend/Date.php';
			$date = new Zend_Date(Zend_Date::now(), Zend_Date::ISO_8601);			
			$pages = new Pages();
			$params = $this->_request->getParams();
			$data = array(
				'parentid'     	=> ($this->_request->getParam("parentid") == "-1") ? NULL : $this->_request->getParam("parentid"),
			    'title'      	=> $this->_request->getParam("title"),
			    'slug' 			=> $this->_request->getParam("slug"),
			    'text'			=> stripslashes($this->_request->getParam("text")),
				'ordering'		=> $this->_request->getParam("ordering"),
				'published'		=> $this->_request->getParam("published"),
				'modified'		=> $date->toString("YYYY-MM-dd HH:mm:ss"),
				'author'		=> Zend_Auth::getInstance()->getIdentity()->fullname,
				'showsub'		=> $this->_request->getParam("showsub")
			);			
		
			if(($this->_request->getParam("slug") != $this->_request->getParam("oldslug")) && $pages->slugExists($this->_request->getParam("slug")))
			{
				$this->view->baseUrl();
				$this->render("slug");
				return;
			}
			else
			{
	
				// New article -> SQL insert the data
				if($this->_request->getParam("id") == "-1")
				{
						$pages->insert($data);
				}
				// Existing article -> SQL update the data
				else
				{
					$where = $pages->getAdapter()->quoteInto('id = ?', $this->_request->getParam("id"));			
					$pages->update($data, $where);
				}
				
				$pages->fixOrdering($data["parentid"]);
				
				if($this->_request->getParam("continue") == "1")
					$redirecturl = 'admin/page/edit/id/' . $this->_request->getParam("id");
				else
					$redirecturl = '/admin/page/';
			}

		}

		$this->_redirect($redirecturl);
	}
	
	
	public function deleteAction()
	{
		if($this->getRequest()->isPost())
		{
			$this->view->deleted = 0;
			$pages = new pages();
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
			$this->view->deleted = $pages->delete($data);
		}
		if($this->view->deleted)
		{
			$this->_redirect("/admin/page");
		}
		else
		{
			$this->view->baseUrl();
		}
	}

	public function publishAction()
	{

		$pages = new Pages();
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
		$pages->setPublish($data, 1);			
		$this->_redirect('admin/page/');


	}
	
	public function unpublishAction()
	{
		$pages = new Pages();
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
		$pages->setPublish($data, 0);
		$this->_redirect('admin/page');
	}
}
