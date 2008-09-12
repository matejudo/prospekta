<?php

require_once('Zend/Controller/Action.php');

class GalleryController extends Zend_Controller_Action
{

	function init()
	{
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl(); 
		$this->_helper->layout->setLayout('gallery'); 
	}
	
	public function indexAction()
	{
		$this->view->baseUrl();
		
		$menu = new Menu();
		$menuitems = $menu->getTree("Glavni");
		$pages = new Pages();
		$this->view->topmenu = "";
		$this->view->submenu = "";
		$counter = 1;
		foreach($menuitems as $item)
		{
			$counter++;
			if($item->target == "-2")
				$path = "#";
			elseif($item->target == "-1")
				$path = $this->view->baseUrl();
			else
				$path = $this->view->baseUrl() . "/" . $pages->getPathById($item->target);
			$this->view->topmenu .= '<li class="topitem" id="menuitem' . $counter . '"><a href="' . $path . '"><strong>' . $item->title . '</strong></a></li>';
			
			$this->view->submenu .= '<ul class="submenu" id="subtab' . $counter . '" style="display: none">';
			foreach($item->children as $subitem)
			{
				if($subitem->target == "-2")
					$path = "#";
				elseif($subitem->target == "-1")
					$path = $this->view->baseUrl();
				else
					$path = $this->view->baseUrl() . "/" . $pages->getPathById($subitem->target);
				$this->view->submenu .= '<li><a href="' . $path . '"><strong>' . $subitem->title . '</strong>' . (($subitem->description != "") ? (" - " . $subitem->description) : "") . '</a></li>';
			}
			$this->view->submenu .= '</ul>';			
		}
		$this->view->menucounter = $counter;
		
	}

}