<?php
	
require_once 'Zend/Controller/Action.php';

class Admin_AuthController extends Zend_Controller_Action
{

	function init()
	{
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
	}

	public function indexAction()
	{
		$this->view->baseUrl();
		
		$username = $this->_getParam("username");
		$password = $this->_getParam("password");
		//Zend_Debug::dump($username . " - " . $password);
		$this->_helper->layout->setLayout('clean'); 
	}
	
	
	function loginAction()
	{
		$this->view->baseUrl();
		$this->_helper->layout->setLayout('clean'); 
		
		$this->view->message = '';
		if ($this->_request->isPost()) 
		{
			// collect the data from the user
			Zend_Loader::loadClass('Zend_Filter_StripTags');
			$f = new Zend_Filter_StripTags();
			$username = $f->filter($this->_request->getPost('username'));
			$password = $f->filter($this->_request->getPost('password'));
			if (empty($username)) 
			{
				$this->view->message = 'Please provide a username.';
			} 
			else if (empty($password)) 
			{
				$this->view->message = 'Please provide a password.';
			} 
			else 
			{
				// setup Zend_Auth adapter for a database table
				Zend_Loader::loadClass('Zend_Auth_Adapter_DbTable');
				$db = Zend_Registry::get('db');
				$authAdapter = new Zend_Auth_Adapter_DbTable($db);
				$authAdapter->setTableName('pros_user');
				$authAdapter->setIdentityColumn('username');
				$authAdapter->setCredentialColumn('password');
				$authAdapter->setCredentialTreatment('md5(?)');
				// Set the input credential values to authenticate against
				$authAdapter->setIdentity($username);
				$authAdapter->setCredential($password);
				// do the authentication
				$auth = Zend_Auth::getInstance();
				$result = $auth->authenticate($authAdapter);
				if ($result->isValid()) 
				{
					// success: store database row to auth's storage
					// system. (Not the password though!)
					$data = $authAdapter->getResultRowObject(null,
					'password');
					$auth->getStorage()->write($data);
					$this->_redirect('/admin');
				} else {
					// failure: clear database row from session
					$this->view->message = 'Login failed.';
				}
			}
		}
		$this->view->title = "Log in";
	}
	
	function logoutAction()
	{
		Zend_Auth::getInstance()->clearIdentity();
		$this->_redirect('/');
	}
	
}