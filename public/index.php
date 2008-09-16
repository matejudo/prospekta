<?php
	error_reporting(E_ALL|E_STRICT);
	ini_set('display_errors', 1);
	date_default_timezone_set('Europe/Zagreb');

	
	// directory setup and class loading
	set_include_path('.' . PATH_SEPARATOR . '../library/'
		. PATH_SEPARATOR . '../application/models'
		. PATH_SEPARATOR . get_include_path());		
	require_once 'Zend/Loader.php';
	Zend_Loader::loadClass('Zend_Auth');
	
	Zend_Loader::registerAutoload();	
	
	try
	{
		// load configuration
		$config = new Zend_Config_Ini('../application/config.ini', 'general');
		$registry = Zend_Registry::getInstance();
		$registry->set('config', $config);
		Zend_Session::start();
		
		// setup the view
		$view = new Zend_View();
		$view->setHelperPath('../application/views/helpers');
		
		// setup database
		$db = Zend_Db::factory($config->db);
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		Zend_Db_Table::setDefaultAdapter($db);
		$db->query("SET NAMES 'utf8' COLLATE 'utf8_unicode_ci'");
		Zend_Registry::set('db', $db);
		
		// Create auth object
		$auth = Zend_Auth::getInstance();
	
		// Front Controller
		$frontController = Zend_Controller_Front::getInstance();
		$router = $frontController->getRouter();
	
		$frontController->addModuleDirectory('../application/modules');
		$frontController->setControllerDirectory(	array(
														"default" => "../application/controllers",
														"admin" => "../application/modules/admin/controllers"
													));
													
		$defaultModules = array(
			'../application',
			'../application/modules/admin'
		);
													
		foreach($defaultModules as $key => $module) {	 
			// add default controller directories
			$frontController->addControllerDirectory($module . DIRECTORY_SEPARATOR . 'controllers', $key);	 
			// add models directory to include path
			set_include_path(get_include_path() . PATH_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'models');
	 
		}									
		
		// učitaj strukturu stranice		
		$pages = new Pages();
		$paths = $pages->getAllPaths();	
	
		foreach($paths as $curpath)
		{			
			$route = new Zend_Controller_Router_Route(	$curpath["path"] . '/',
														array(	'module' => 'default',
																'controller' => 'page', 
																'action' => 'index', 
																'id' => $curpath["id"]));
			$router->addRoute($curpath["slug"], $route);
		}
		
		$route = new Zend_Controller_Router_Route(	'article/:slug',
													array(	'module' => 'default',
															'controller' => 'article', 
															'action' => 'index'));
		$router->addRoute("article", $route);
		
		$route = new Zend_Controller_Router_Route(	'article/comment/',
													array(	'module' => 'default',
															'controller' => 'article', 
															'action' => 'comment'));
		$router->addRoute("comment", $route);
		
		$route = new Zend_Controller_Router_Route(	'anketa/:action/*',
													array(	'module' => 'default',
															'controller' => 'poll'));
		$router->addRoute("anketa", $route);
		
		$route = new Zend_Controller_Router_Route(	'admin/article/category/:name',
													array(	'module' => 'admin',
															'controller' => 'article',
															'action' => 'category'),
													array(	'slug' => '(.*)'));
		$router->addRoute("admincat", $route);

		$route = new Zend_Controller_Router_Route_Regex(	'admin/file/folder/(.*)',
															array(	'module' => 'admin',
																	'controller' => 'file',
																	'action' => 'folder'),
															array(1 => 'curdir')
															);
		$router->addRoute("adminfolder", $route);
		
		$route = new Zend_Controller_Router_Route_Regex(	'admin/file/folderclear/(.*)',
															array(	'module' => 'admin',
																	'controller' => 'file',
																	'action' => 'folderclear'),
															array(1 => 'curdir')
															);
		$router->addRoute("adminfolderclear", $route);
		
		//Zend_Debug::dump($router);
	
		
		// setup controller
		$frontController->throwExceptions(true);
		Zend_Layout::startMvc(array('layoutPath'=>'../application/layouts'));
		
		// run!
		$frontController->dispatch();
		
	}
	catch (Exception $ex)
	{
		$contentType = 'text/html';
		header("Content-Type: $contentType; charset=utf-8");
		echo 'Došlo je do pogreške. Molimo da probate ponovo.';
		echo '<h2 style="display: block;">Unexpected Exception: ' . $ex->getMessage() . '</h2><pre style="display: block;">';
		echo $ex->getTraceAsString();
	}
	
