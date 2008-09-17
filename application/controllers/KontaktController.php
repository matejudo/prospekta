<?php

require_once 'Zend/Controller/Action.php';

class KontaktController extends Zend_Controller_Action
{
	function init()
	{
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
		
		$menu = new Menu();		
		$ret = $menu->render("Glavni", $this->view->baseUrl);
		$this->view->topmenu = $ret->topmenu;
		$this->view->submenu = $ret->submenu;
		$this->view->menucounter = $ret->counter;
		
		$this->view->leftmenu = $menu->render("Lijevi", $this->view->baseUrl);
	}
	
	public function preporuciAction()
	{
		$this->view->baseUrl();
		$this->view->path = array(array("path" => "kontakt/preporuci", "title" => "Preporuči Prospektu prijatelju "));
	}
	
	public function posaljiAction()
	{
		$this->view->baseUrl();
		$this->view->path = array(array("path" => "kontakt/posalji", "title" => "Pošalji članak prijatelju"));
		$id = $this->_getParam("id");
		if($id != NULL)
		{
			$articles = new Articles();
			$article = $articles->getById($id, 0);
			$this->view->article = $article->title . "\n\n" . $article->text;
			$this->view->article = str_replace("</p>", "\n", $this->view->article);
			$this->view->article = str_replace("<br />", "\n", $this->view->article);
			$this->view->article = strip_tags($this->view->article);
			$this->view->slug = $article->slug;
		}
		else
		{
			$this->_redirect("kontakt");
		}
	}
	
	public function posalji2Action()
	{
		$this->view->baseUrl();
		$this->view->path = array(array("path" => "kontakt/posalji", "title" => "Pošalji članak prijatelju"));
		if($this->getRequest()->isPost())
		{
			$ime = $this->_getParam("ime");
			$email = $this->_getParam("email");
			$clanak = $this->_getParam("clanak");
			$slug = $this->_getParam("slug");
			
			if($ime == "" || $email == "" || $clanak == "")
			{
				$this->view->result = "<h1>Greška =(</h1><p>Potrebno je ispuniti sva polja osobnih podataka.</p>";
			}
			else
			{
				$body = "Postovani, osoba koja se predstavila kao $ime, salje Vam sljedeci clanak sa stranice http://www.prospekta.net\n"
					  . "Clanak u cijelosti procitajte na " . $this->view->baseUrl . "/clanak/" . $slug . "\n\n"
					  . "Lijep pozdrav, Vasa Prospekta.\n\n"
					  . "\n-----------------------------\n"
					  . $clanak
					  . "\n-----------------------------\n"
					  . "Prospekta – edukacija i zaposljavanje mladih\n"
					  . "Ljudevita Posavskog 11\n"
					  . "10 000 Zagreb\n"
					  . "Tel: +385 (0)1 4658 015\n"
					  . "Mob: 095/6200005\n"
					  . "www.prospekta.net";
				
				$mail = new Zend_Mail();					  
				$mail->setBodyText($body);
				$mail->setFrom('noreply@prospekta.net', 'Prospekta');
				$mail->addTo($email); //ivanap@horizont.hr
				$mail->setSubject("Clanak sa prospekta.net");
				try
				{
					$mail->send();
					$this->view->result = "<h1>Potvrda</h1><p>Prijava je uspješno poslana. Zahvaljujemo.</p>";
				}
				catch (Exception $ex)
				{
					$this->view->result = "<h1>Greška =(</h1><p>Došlo je do greške tijekom prijave. Molimo da se prijavite osobno na email <a href='mailto:ivanap@horizont.hr'>ivanap@horizont.hr</a>.</p>";
				}

			}
		}
		else
		{
			$this->view->result = "<h1>Greška =(</h1><p>Došlo je do greške. Molimo da se prijavite osobno na email <a href='mailto:ivanap@horizont.hr'>ivanap@horizont.hr</a></p>";
		}
	}
	
	public function preporukaAction()
	{
		$this->view->baseUrl();
		$this->view->path = array(array("path" => "kontakt/preporuci", "title" => "Preporuči Prospektu prijatelju "));	
		if($this->getRequest()->isPost())
		{
			$name = $this->_getParam("name");
			$email = $this->_getParam("email");
			if($name == "" || $email == "")
			{
				$this->view->result = "<h1>Greška =(</h1><p>Potrebno je ispuniti obadva polja.</p>";
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
					$this->view->result = "<h1>Potvrda</h1><p>Email je uspješno poslan na adresu <code>$email</code>.</p>";
				}
				catch (Exception $ex)
				{
					$this->view->result = "<h1>Greška =(</h1><p>Došlo je do greške prilikom slanja. Molimo da pokušate ponovo kasnije.</p>";
				}

			}
		}
		else
		{
			$this->view->result = "<h1>Greška =(</h1><p>Došlo je do greške.</p>";
		}
	}
	
	public function prijaveAction()
	{
		$this->view->baseUrl();
		$this->view->path = array(array("path" => "kontakt/prijave", "title" => "Prijave"));
	}

	public function prijave2Action()
	{
		$this->view->baseUrl();
		$this->view->path = array(array("path" => "kontakt/prijave", "title" => "Prijave"));
		if($this->getRequest()->isPost())
		{
			$ime = $this->_getParam("ime");
			$prezime = $this->_getParam("prezime");
			$email = $this->_getParam("email");
			$tel = $this->_getParam("tel");
			$career = $this->_getParam("career");
			$selfdev = $this->_getParam("selfdev");
			$student = $this->_getParam("student");
			
			if($ime == "" || $prezime == "" || $email == "" || $tel == "")
			{
				$this->view->result = "<h1>Greška =(</h1><p>Potrebno je ispuniti sva polja osobnih podataka.</p>";
			}
			else
			{
				$body = "Online prijava:\n\n"
					  . $this->_getParam("ime") . " " . $this->_getParam("prezime")  . "\n"
					  . "E-mail: " . $this->_getParam("email") . "\n"
					  . "Tel./Mob." . $this->_getParam("tel") . "\n";
				$body .= "\n\nPrijava za programe:\n";
				if($career)  $body .= "  Career coaching\n";
				if($selfdev) $body .= "  Self-development\n";
				if($student) $body .= "  Student management\n";
				$body .= "\n";
				
				$body .= "Komentar/Upit: \n" 
					  . $this->_getParam("info");
				
				$mail = new Zend_Mail();					  
				$mail->setBodyText($body);
				$mail->setFrom('noreply@prospekta.net', 'Prospekta');
				$mail->addTo("matej.udo@gmail.com", "Ivana Pezic"); //ivanap@horizont.hr
				$mail->setSubject("[Web Prijava] ");
				try
				{
					$mail->send();
					$this->view->result = "<h1>Potvrda</h1><p>Prijava je uspješno poslana. Zahvaljujemo.</p>";
				}
				catch (Exception $ex)
				{
					$this->view->result = "<h1>Greška =(</h1><p>Došlo je do greške tijekom prijave. Molimo da se prijavite osobno na email <a href='mailto:ivanap@horizont.hr'>ivanap@horizont.hr</a>.</p>";
				}

			}
		}
		else
		{
			$this->view->result = "<h1>Greška =(</h1><p>Došlo je do greške. Molimo da se prijavite osobno na email <a href='mailto:ivanap@horizont.hr'>ivanap@horizont.hr</a></p>";
		}
	}
}