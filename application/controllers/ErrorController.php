<?php
/**
* ErrorController - The default error controller class
*
* File: application/controllers/ErrorController.php
*
*/

require_once 'Zend/Controller/Action.php' ;
require_once 'Zend/Log.php';
require_once 'Zend/Log/Writer/Stream.php';


class ErrorController extends Zend_Controller_Action
{

	function init()
	{
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl(); 
		$this->_helper->layout->setLayout('gallery'); 
		$menu = new Menu();
		
		$ret = $menu->render("Glavni", $this->view->baseUrl);
		$this->view->topmenu = $ret->topmenu;
		$this->view->submenu = $ret->submenu;
		$this->view->menucounter = $ret->counter;
		
		$this->view->leftmenu = $menu->render("Lijevi", $this->view->baseUrl);
		
		
	}

    public function errorAction()
    {
    	$this->view->baseUrl();
    	$this->view->path = array(array("path" => "error", "title" => "Greška"));
        $errors = $this->_getParam('error_handler');

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');

                $content = "<h1>Ups! Greškica...</h1><p>Stranicu koju ste tražili nije moguće pronaći. Stranica je možda maknuta, promijenjeno joj je ime ili je privremeno nedostupna. Možete pokušati nešto od navedenog:</p>"
                		 . "<ul>"
                		 . "<li>Ukoliko ste utipkali adresu stranice, provjerite da je adresa pravilno napisana</li>"
                		 . "<li>Otvorite <a href='".$this->view->baseUrl."'>početnu stranicu</a> i potražite linkove na informacije koje vas zanimaju</li>"
                		 . "</ul>";
                break;
            default:
                // application error
                $content ="<h1>Ups! Greškica...</h1><p>Nešto smo gadno zabrljali... Molim te probaj ponovo kasnije.</p>";
                break;
        }

        // Clear previous content
        $this->getResponse()->clearBody();

        $this->view->content = $content;
    }
}
