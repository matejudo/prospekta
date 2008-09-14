<?php

require_once('Zend/Controller/Action.php');

class PageController extends Zend_Controller_Action
{

	function init()
	{
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl(); 
	}
	
	public function indexAction()
	{
		$this->view->baseUrl();
		$this->view->id = $this->_getParam("id");
		$pages = new Pages();
		$this->view->page = $pages->getById($this->view->id);
		$this->view->subelements = $pages->getChildrenById($this->view->id);
		$this->view->path = array_reverse($pages->getBreadcrumbs($this->view->id));
		
		$sidebars = new Sidebar();
//		$this->view->leftsidebar = $sidebars->get($this->view->id, "left");
//		$this->view->rightsidebar = $sidebars->get($this->view->id, "right");
		$this->view->leftsidebar = $sidebars->render($this->view->id, "Lijevo", $this->view->baseUrl);
		$this->view->rightsidebar = $sidebars->render($this->view->id, "Desno", $this->view->baseUrl);

		
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
		
		
		
		$leftmenuitems = $menu->getTree("Lijevi");
		$this->view->leftmenu = "";
		$counter = 1;
		foreach($leftmenuitems as $item)
		{
			$counter++;
			if($item->target == "-2")
				$path = "#";
			elseif($item->target == "-1")
				$path = $this->view->baseUrl();
			else
				$path = $this->view->baseUrl() . "/" . $pages->getPathById($item->target);
				
	
			
			if($item->children)
			{
				$this->view->leftmenu .= '<li style="background: url(\''.$this->view->baseUrl() . '/images/' . $item->description.'\') 0px 8px no-repeat;">';
				$this->view->leftmenu .= '<a style="padding-left: 20px;" href="#" onclick="$(\'#sidesubtab'.$counter.'\').showsubmenu(); return false;">' . $item->title . '</a>';
				$this->view->leftmenu .= '<ul class="sidesubmenu" id="sidesubtab'.$counter.'" style="display: none;">';			
					foreach($item->children as $subitem)
					{
						if($subitem->target == "-2")
							$path = "#";
						elseif($subitem->target == "-1")
							$path = $this->view->baseUrl();
						else
							$path = $this->view->baseUrl() . "/" . $pages->getPathById($subitem->target);
						$this->view->leftmenu .= '<li><a href="' . $path . '">' . $subitem->title . '</a></li>';
					}
				$this->view->leftmenu .= '</ul>';
				$this->view->leftmenu .= '</li>';
			}
			else
			{
				$this->view->leftmenu .= '<li style="background: url(\''.$this->view->baseUrl() . '/images/' . $item->description.'\') 0px 8px no-repeat;">';
				$this->view->leftmenu .= '<a style="padding-left: 20px;" href="' . $path . '">' . $item->title . '</a></li>';
			}
			
						
		}		
	}

}