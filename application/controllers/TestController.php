<?php

require_once 'Zend/Controller/Action.php';

class TestController extends Zend_Controller_Action
{
	public function indexAction()
	{
		$this->_setParam("data", "roflmaoroflmaoroflmao");
		$this->_forward("output", "target");
	}
}