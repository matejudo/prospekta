<?php

require_once 'Zend/Controller/Action.php';

class ArticleController extends Zend_Controller_Action
{

	function init()
	{
		$this->view->baseUrl = $this->_request->getBaseUrl();
	}
	
	public function indexAction()
	{
		$this->view->baseUrl();
		$this->view->slug = $this->_getParam("slug");
		$articles = new Articles();
		$this->view->article = $articles->getBySlug($this->view->slug, 1);
		$this->view->path = array(array("path" => "article/" . $this->view->article->slug, "title" => "Članak: " . $this->view->article->title));
		
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