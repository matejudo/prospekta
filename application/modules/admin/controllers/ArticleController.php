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
	
	public function publishAction()
	{
		if($this->getRequest()->isPost())
		{
			$params = $this->_request->getParams();
			foreach($params as $param)
			{
				Zend_Debug::dump($param);
			}
		}
		else
		{
			$data = array("published" => 1);
		}
		$articles = new Articles();
		$id = $this->_getParam("id");
		$where = $articles->getAdapter()->quoteInto('id = ?', $id);
		$articles->update($data, $where);
		$this->_redirect('admin/article');
	}
	
	public function unpublishAction()
	{
		if($this->getRequest()->isPost())
		{
			$params = $this->_request->getParams();
			foreach($params as $param)
			{
				Zend_Debug::dump($param);
			}
		}
		else
		{
			$data = array("published" => 0);
		}
		$articles = new Articles();
		$id = $this->_getParam("id");
		$where = $articles->getAdapter()->quoteInto('id = ?', $id);
		$articles->update($data, $where);
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

}