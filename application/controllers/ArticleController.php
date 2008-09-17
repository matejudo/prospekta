<?php

require_once 'Zend/Controller/Action.php';

class ArticleController extends Zend_Controller_Action
{

	function init()
	{
		$this->view->baseUrl = $this->_request->getBaseUrl();
		$menu = new Menu();
		
		$ret = $menu->render("Glavni", $this->view->baseUrl);
		$this->view->topmenu = $ret->topmenu;
		$this->view->submenu = $ret->submenu;
		$this->view->menucounter = $ret->counter;
		
		$this->view->leftmenu = $menu->render("Lijevi", $this->view->baseUrl);
	}
	
	public function commentAction()
	{
		if($this->getRequest()->isPost())
		{		
			require_once 'Zend/Date.php';
			$date = new Zend_Date(Zend_Date::now(), Zend_Date::ISO_8601);			
			$comments = new Comments();
			$params = $this->_request->getParams();
			$data = array(
			    'article_id'   	=> $this->_request->getParam("article_id"),
			    'fullname'		=> $this->_request->getParam("fullname"),
				'email'			=> $this->_request->getParam("email"),
			    'text'			=> stripslashes($this->_request->getParam("text")),
				'website'		=> $this->_request->getParam("website")
			);
			if($data['website'] == "http://") $data['website'] = "";
 
			$comments->insert($data); 
		}
		$this->_redirect("/article/" . $this->_getParam("slug"));
	}
	
	public function indexAction()
	{
		$this->view->baseUrl();
		if($this->_getParam("slug") == "") $this->_redirect("/");
		$this->view->slug = $this->_getParam("slug");		
		$articles = new Articles();
		$this->view->article = $articles->getBySlug($this->view->slug, 1);
		$this->view->path = array(array("path" => "article/" . $this->view->article->slug, "title" => "Članak: " . $this->view->article->title));
		if(!isset($this->view->article->error))
		{
			$comments = new Comments();
			$this->view->comments = $comments->get($this->view->article->id);
		}
		



	}
}