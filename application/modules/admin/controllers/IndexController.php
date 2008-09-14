<?php
	
require_once 'Zend/Controller/Action.php';

class Admin_IndexController extends Zend_Controller_Action
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
		$settings = new Settings();
		$this->view->intro = $settings->get("front_intro");
		$this->view->career = $settings->get("front_career");
		$this->view->self = $settings->get("front_self");
		$this->view->student = $settings->get("front_student");
		$this->view->career_date = $settings->get("front_career_date");
		$this->view->self_date = $settings->get("front_self_date");
		$this->view->student_date = $settings->get("front_student_date");
	}
	
	public function savefrontAction()
	{
		$settings = new Settings();
		$this->view->intro = $settings->set("front_intro", $this->_request->getParam("front_intro", NULL));
		$this->view->career = $settings->set("front_career", $this->_request->getParam("front_career", NULL));
		$this->view->self = $settings->set("front_self", $this->_request->getParam("front_self", NULL));
		$this->view->student = $settings->set("front_student", $this->_request->getParam("front_student", NULL));
		$this->view->career_date = $settings->set("front_career_date", $this->_request->getParam("front_career_date", NULL));
		$this->view->self_date = $settings->set("front_self_date", $this->_request->getParam("front_self_date", NULL));
		$this->view->student_date = $settings->set("front_student_date", $this->_request->getParam("front_student_date", NULL));
		$this->_redirect("/admin/");
	}

}