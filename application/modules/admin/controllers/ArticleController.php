<?php

require_once 'Zend/Controller/Action.php';

class Admin_ArticleController extends Zend_Controller_Action
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
		
	}
	
	public function categoryAction()
	{
		$this->view->baseUrl();
		$cat = $this->_getParam('name', 'Novosti');
		$this->view->category = $cat;
		$articles = new Articles();
		$this->view->articles = $articles->fetchCategory($cat);
	}
	
	public function newAction()
	{
		$this->view->baseUrl();
		$this->view->category = $this->_getParam('category', '');
		$editor = new TextEditor();
		$this->view->editor = $editor->getHTML("text", "");
		$this->view->article = new stdClass();
		$this->view->article->title = "";
		$this->view->article->slug = "";
		$this->view->article->text = "";
		$this->view->article->published = "1";
		$this->view->article->comments = "1";
		$this->view->article->id = "-1";
		$this->view->article->category = $this->view->category;
		$this->render("edit");
	}
	
	public function editAction()
	{
		$this->view->baseUrl();
		$id = $this->_getParam("id");
		$articles = new Articles();
		$this->view->article = $articles->getById($id);
		$this->view->categories = $articles->getCategories();
		$editor = new TextEditor();		
		$this->view->editor = $editor->getHTML("text", $this->view->article->text);
	}
	
	public function publishAction()
	{

		$articles = new Articles();
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
		$articles->setPublish($data, 1);			
		$this->_redirect('admin/article/', array("message" => "Article(s) published"));


	}
	
	public function unpublishAction()
	{
		$articles = new Articles();
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
		$articles->setPublish($data, 0);
		$this->_redirect('admin/article', array("message" => "Article(s) unpublished"));
	}
	
	public function deleteAction()
	{
		if($this->getRequest()->isPost())
		{
			$articles = new Articles();
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
			$articles->delete($data, 0);
		}
		$this->_redirect('admin/article');
	}
	
	
	public function moveupAction()
	{
		$articles = new Articles();
		$id = $this->_getParam("id");
		$articles->moveup($id);		
		$this->_redirect('admin/article');
	}
	
	public function movedownAction()
	{
		$articles = new Articles();
		$id = $this->_getParam("id");
		$articles->movedown($id);		
		$this->_redirect('admin/article');
	}
	
	public function saveAction()
	{

		if($this->getRequest()->isPost())
		{
		
			require_once 'Zend/Date.php';
			$date = new Zend_Date(Zend_Date::now(), Zend_Date::ISO_8601);			
			$articles = new Articles();
			$params = $this->_request->getParams();
			$data = array(
			    'title'      	=> $this->_request->getParam("title"),
			    'slug' 			=> $this->_request->getParam("slug"),
			    'text'			=> $this->_request->getParam("text"),
				'category'		=> $this->_request->getParam("category"),
				'published'		=> $this->_request->getParam("published"),
				'comments'		=> $this->_request->getParam("comments"),
				'modified'		=> $date->toString("YYYY-MM-dd HH:mm:ss")
			);	
		
		
			// New article -> SQL insert the data
			if($this->_request->getParam("id") == "-1")
			{
				$articles->insert($data); 
			}
			// Existing article -> SQL update the data
			else
			{
				$where = $articles->getAdapter()->quoteInto('id = ?', $this->_request->getParam("id"));			
				$articles->update($data, $where);
			}
			
			if($this->_request->getParam("continue") == "1")
				$redirecturl = 'admin/article/edit/id/' . $this->_request->getParam("id");
			else
				$redirecturl = '/admin/article/category/' . $this->_request->getParam("category");
		}

		$this->_redirect($redirecturl);
	}
}