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
		
		$articles = new Articles();
		$this->view->articles = $articles->fetchCategory("news");
		
	}
	
	public function newAction()
	{
		$this->view->baseUrl();
		$users = new Users();
		$this->view->users = $users->getUsers();
	}
	
	public function publishAction()
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
			array_push( $data, $this->_getParam("id") );
		}
		
		$articles->setPublish($data, 1);
		Zend_Debug::dump($articles);
		$this->_redirect('admin/article');


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
		$this->_redirect('admin/article');
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
			$articles = new Articles();
			$params = $this->_request->getParams();
			Zend_Debug::dump($params);
			$data = array(
			    'title'      	=> $this->_request->getParam("title"),
			    'slug' 			=> $this->_request->getParam("slug"),
			    'text'			=> $this->_request->getParam("text"),
				'category'		=> $this->_request->getParam("category"),
				'published'		=> $this->_request->getParam("published")
			);
			$articles->insert($data); 
		}
		$this->_redirect('admin/article');
	}

}