<?php

/**
 * AdminController
 * 
 * @author
 * @version 
 */

require_once 'Zend/Controller/Action.php';

class AdminController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	
	
	public function indexAction() {
		$this->view->baseUrl();
	}

}
