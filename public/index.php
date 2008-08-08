<?php
	error_reporting(E_ALL|E_STRICT);
	ini_set('display_errors', 1);
	date_default_timezone_set('Europe/Zagreb');

	
	// directory setup and class loading
	set_include_path('.' . PATH_SEPARATOR . '../library/'
		. PATH_SEPARATOR . '../application/models'
		. PATH_SEPARATOR . get_include_path());		
	require_once 'Zend/Loader.php';
	
	Zend_Loader::registerAutoload();	
	
	try
	{
		// load configuration
		$config = new Zend_Config_Ini('../application/config.ini', 'general');
		$registry = Zend_Registry::getInstance();
		$registry->set('config', $config);
		
		// setup the view
		$view = new Zend_View();
		$view->setHelperPath('../application/views/helpers');
		
		// setup database
		$db = Zend_Db::factory($config->db);
		Zend_Db_Table::setDefaultAdapter($db);
		$db->query("SET NAMES 'utf8' COLLATE 'utf8_unicode_ci'");
	
		// setup custom router
		require_once '../application/catRoute.php';
		$frontController = Zend_Controller_Front::getInstance();
		$router = $frontController->getRouter();
	
		$frontController->addModuleDirectory('../application/modules');
		$frontController->setControllerDirectory(	array(
														"default" => "../application/controllers",
														"admin" => "../application/modules/admin/controllers"
													));
		
		// učitaj strukturu stranice
		
		$struct = new Structure();
		$mainmenu = $struct->getChildren("prospekta");	
	
		foreach($mainmenu as $menuitem)
		{			
			$route = new Zend_Controller_Router_Route(	$menuitem->slug . '/:slug/*',
														array(	'module' => 'default',
																'controller' => 'cat', 
																'action' => 'index', 
																'slug' => null,
																'topslug' => $menuitem->slug),
														array(	'slug' => '(.*)'));
			$router->addRoute($menuitem->slug, $route);
		}
		
			$route = new Zend_Controller_Router_Route(	'woot/ffs/lol/wtf',
												array(	'module' => 'default',
														'controller' => 'cat', 
														'action' => 'index', 
														'slug' => 'wtf',
														'topslug' => 'wtf'),
												array(	'slug' => '(.*)'));
			$router->addRoute($menuitem->slug, $route);
		
//		$route = new Zend_Controller_Router_Route(	'admin/:controller/:action/*',
//    												array(	'module' => 'admin')
//													);
//		$router->addRoute('admin', $route);
	
	
	
		
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
		echo 'an unexpected error occurred.';
		echo '<h2>Unexpected Exception: ' . $ex->getMessage() . '</h2><br /><pre>';
		echo $ex->getTraceAsString();
	}
	
