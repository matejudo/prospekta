<?php

require_once "Zend/Controller/Action.php";

class PollController extends Zend_Controller_Action
{

	function init()
	{
		$this->view->baseUrl = $this->_request->getBaseUrl();
	}
	
	public function indexAction()
	{
	
	}
	
	public function voteAction()
	{
		$poll = $this->_request->getParam("pollid", -1);
		$returnto = $this->_request->getParam("returnto", "/");
		if($poll < 0)
			return;
		$vote = $this->_request->getParam("answer", array());
		$this->view->path = array(array("path" => "anketa", "title" => "Anketa"));
		$polls = new Polls();
		$this->view->inlist = "...";
		$polls->vote($poll, $vote);
		// TODO: Set Cookie
		setcookie("prospoll".$poll, "1", time()+60*60*24*30, "/"); //, "/", "localhost"
		
		header('Location: ' . $returnto);
		
	}
}