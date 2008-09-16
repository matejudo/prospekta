<?php

require_once 'Zend/Controller/Action.php';

class KontaktController extends Zend_Controller_Action
{

	function init()
	{
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
		
	}
	
	public function indexAction()
	{

	}
	
	public function preporukaAction()
	{
		$this->view->baseUrl();
		if($this->getRequest()->isPost())
		{
			$name = $this->_getParam("name");
			$email = $this->_getParam("email");
			if($name == "" || $email == "")
			{
				$this->view->result = "<p>Potrebno je ispuniti obadva polja.</p>";
			}
			else
			{
				$body = "Postovani, osoba koja se predstavila kao $name, preporucuje Vam da posjetite web stranicu http://www.prospekta.net\n\n"
					  . "Lijep pozdrav, Vasa Prospekta.\n\n"
					  . "Prospekta – edukacija i zaposljavanje mladih\n"
					  . "Ljudevita Posavskog 11\n"
					  . "10 000 Zagreb\n"
					  . "Tel: +385 (0)1 4658 015\n"
					  . "Mob: 095/6200005\n"
					  . "www.prospekta.net";
				
				$mail = new Zend_Mail();					  
				$mail->setBodyText($body);
				$mail->setFrom('noreply@prospekta.net', 'Prospekta');
				$mail->addTo($email);
				$mail->setSubject("Prijatelj Vam salje link");
				try
				{
					$mail->send();
					$this->view->result = "<p>Email je uspješno poslan na adresu <code>$email</code>.</p>";
				}
				catch (Exception $ex)
				{
					$this->view->result = "<p>Došlo je do greške prilikom slanja. Molimo da pokušate ponovo kasnije.</p>";
				}

			}
		}
		else
		{
			$this->view->result = "<p>Došlo je do greške.</p>";
		}
	}
}