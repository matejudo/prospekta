<?php

require_once 'Zend/Controller/Action.php';

class Admin_SidebarController extends Zend_Controller_Action
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
		$sidebars = new Sidebar();
		$this->view->sidebars = $sidebars->fetchAllCount();
	}
	
	public function edittextAction()
	{
		$sidebars = new Sidebar();
		$id = $this->_getParam("id");
		$this->view->sidebar = $sidebars->getById($id);
		$editor = new TextEditor();		
		$this->view->editor = $editor->getHTML("text", $this->view->sidebar->text, "simple");
		$pages = new Pages();
		$this->view->pages = $pages->getFlatTree();
		$this->view->showpages = $sidebars->getPages($id);
	}
	
	public function newtextAction()
	{

		$this->view->sidebar = new stdClass();
		$this->view->sidebar->title = "";
		$this->view->sidebar->text = "";
		$this->view->sidebar->position = "Desno";
		$this->view->sidebar->published = "1";
		$this->view->sidebar->id = "-1";
		$editor = new TextEditor();
		$this->view->editor = $editor->getHTML("text", $this->view->sidebar->text, "simple");
		$pages = new Pages();
		$this->view->pages = $pages->getFlatTree();
		$this->view->showpages = array();
		$this->render("edittext");
	}
	
	public function savetextAction()
	{
		if($this->getRequest()->isPost())
		{
			$sidebars = new Sidebar();
			$params = $this->_request->getParams();
			$data = array(
			    'title'      	=> $this->_request->getParam("title"),
			    'text'			=> stripslashes($this->_request->getParam("text")),
				'position'		=> $this->_request->getParam("position"),
				'published'		=> $this->_request->getParam("published", "0"),
				'type'			=> $this->_request->getParam("type", "Tekst")
			);

			// New sidebar -> SQL insert the data
			if($this->_request->getParam("id") == "-1")
			{
				$sidebars->insert($data); 
				$id = $sidebars->getAdapter()->lastInsertId();
			}
			// Existing article -> SQL update the data
			else
			{
				$where = $sidebars->getAdapter()->quoteInto('id = ?', $this->_request->getParam("id"));			
				$sidebars->update($data, $where);
				$id = $this->_request->getParam("id");
			}

			// Create 1:N relation to pages			
			$pages = $this->_request->getParam("page");
			$sidebars->setPages($id, $pages);
			$redirecturl = '/admin/sidebar/';

		}

		$this->_redirect($redirecturl);
	}
	
	public function publishAction()
	{

		$sidebars = new Sidebar();
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
		$sidebars->setPublish($data, 1);			
		$this->_redirect('admin/sidebar/');
	}
	
	public function unpublishAction()
	{

		$sidebars = new Sidebar();
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
		$sidebars->setPublish($data, 0);			
		$this->_redirect('admin/sidebar/');
	}
	
	public function moveupAction()
	{
		$sidebars = new Sidebar();
		$id = $this->_getParam("id");
		$sidebars->moveup($id);		
		$this->_redirect('admin/sidebar');
	}
	
	public function movedownAction()
	{
		$sidebars = new Sidebar();
		$id = $this->_getParam("id");
		$sidebars->movedown($id);		
		$this->_redirect('admin/sidebar');
	}
	
	
	public function editpollAction()
	{
		$sidebars = new Sidebar();
		$id = $this->_getParam("id");
		$this->view->sidebar = $sidebars->getById($id);

		$polls = new Polls();
		$this->view->poll = $polls->get($this->view->sidebar->text);
		$this->view->answers = $polls->getAnswers($this->view->sidebar->text);
		
		$pages = new Pages();
		$this->view->pages = $pages->getFlatTree();
		$this->view->showpages = $sidebars->getPages($id);
	}
	
	public function newpollAction()
	{

		$this->view->sidebar = new stdClass();
		$this->view->sidebar->title = "";
		$this->view->sidebar->text = "";
		$this->view->sidebar->position = "Desno";
		$this->view->sidebar->published = "1";
		$this->view->sidebar->id = "-1";

		$this->view->poll = new stdClass();
		$this->view->poll->id = "-1";
		$this->view->poll->question = "";
		$this->view->poll->multiple = 0;
		$this->view->poll->count = 0;
		$this->view->answers = array();
		
		$pages = new Pages();
		$this->view->pages = $pages->getFlatTree();
		$this->view->showpages = array();
		$this->render("editpoll");
	}
	
	
	public function savepollAction()
	{
		if($this->getRequest()->isPost())
		{
			$sidebars = new Sidebar();
			$polls = new Polls();
			$params = $this->_request->getParams();
			$data = array(
			    'title'      	=> $this->_request->getParam("title"),
			    'text'			=> $this->_request->getParam("pollid"),
				'position'		=> $this->_request->getParam("position"),
				'published'		=> $this->_request->getParam("published", "0"),
				'type'			=> $this->_request->getParam("type")
			);

			// New sidebar -> SQL insert the data
			if($this->_request->getParam("id") == "-1")
			{
				$polldata = array(
				    'question'     	=> $this->_request->getParam("question", "Bez pitanja"),
				    'multiple'		=> $this->_request->getParam("multiple", "0"),
					'count'			=> $this->_request->getParam("pollcount", "0"),
				);
				$polls->insert($polldata);
				$pollid = $polls->getAdapter()->lastInsertId();
				
				$data["text"] = $pollid;
				$sidebars->insert($data); 
				$id = $sidebars->getAdapter()->lastInsertId();
				
				$answers = $this->_request->getParam("answers");
				$answerscount = $this->_request->getParam("answerscount");
				
				$polls->saveAnswers($pollid, $answers, $answerscount);
				
			}
			// Existing article -> SQL update the data
			else
			{
				$where = $sidebars->getAdapter()->quoteInto('id = ?', $this->_request->getParam("id"));			
				$sidebars->update($data, $where);
				$id = $this->_request->getParam("id");
				
				$polldata = array(
				    'question'     	=> $this->_request->getParam("question"),
				    'multiple'		=> $this->_request->getParam("multiple"),
					'count'			=> $this->_request->getParam("pollcount"),
				);
				$where = $polls->getAdapter()->quoteInto('id = ?', $this->_request->getParam("pollid"));	
				$polls->update($polldata, $where);
				
				$answers = $this->_request->getParam("answers");
				$answerscount = $this->_request->getParam("answerscount");
				$pollid = $this->_request->getParam("pollid");
				$polls->saveAnswers($pollid, $answers, $answerscount);
			}

			// Create 1:N relation to pages			
			$pages = $this->_request->getParam("page");
			$sidebars->setPages($id, $pages);
			$redirecturl = '/admin/sidebar/';

		}

		$this->_redirect($redirecturl);
	}
	
	public function deleteAction()
	{
		if($this->getRequest()->isPost())
		{
			$sidebars = new Sidebar();
			$data = array();
			$params = $this->_request->getParams();			
			foreach($params as $key => $value)
			{
				if($value == "1")
				{
					$id = substr($key, 2);
					$sidebar = $sidebars->getById($id);
					if($sidebar->type == "Anketa")
					{
						$polls = new Polls();
						$polls->delete($sidebar->text);
					}
					array_push($data, $id);
				}
			}
			$sidebars->delete($data, 0);
		}
		$this->_redirect('admin/sidebar');
	}
	
}