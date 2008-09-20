<?php

require_once 'Zend/Controller/Action.php';

class Admin_FileController extends Zend_Controller_Action
{
	function preDispatch()
	{
		$auth = Zend_Auth::getInstance();
		if (!$auth->hasIdentity()) {
			$this->_redirect('admin/auth/login');
		}
	}

	function init()
	{
		$this->initView();
		$this->view->baseUrl = $this->_request->getBaseUrl();
		$this->_helper->layout->setLayout('admin'); 
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
	}

	public function indexAction()
	{
		$files = new Files();
		$this->view->files = $files->listFiles();
	}
	

	public function folderclearAction()
	{
		$this->_helper->layout->setLayout('admin_no_ui'); 
		$files = new Files();
		$curdir = $this->_getParam("curdir");	
		$this->view->files = $files->listFiles($curdir);
		$this->view->folder = $curdir;	
		$this->render("indexclear");
	}
	
	public function folderAction()
	{
		$files = new Files();
		$curdir = $this->_getParam("curdir");	
		$this->view->files = $files->listFiles($curdir);
		$this->view->folder = $curdir;	
		$this->render("index");
	}
	
	public function uploadAction()
	{
		$curdir = $this->_getParam("curdir");
		$uploaddir = 'uploads/';
		if($curdir) $uploaddir .= $curdir . "/";
		$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);
		echo $uploadfile;
		echo '<pre>';
		if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
		    echo "File is valid, and was successfully uploaded to " . realpath($uploadfile);
		} else {
			switch ($_FILES['userfile'] ['error'])
			 {  case 1:
			           print '<p> The file is bigger than this PHP installation allows</p>';
			           break;
			    case 2:
			           print '<p> The file is bigger than this form allows</p>';
			           break;
			    case 3:
			           print '<p> Only part of the file was uploaded</p>';
			           break;
			    case 4:
			           print '<p> No file was uploaded</p>';
			           break;
			 }
		}
		
		echo 'Here is some more debugging info:';
		print_r($_FILES);
		
		print "</pre>";
		$this->_redirect("/admin/file/folder/$curdir");
	}
	
	public function uploadclearAction()
	{
		$curdir = $this->_getParam("curdir");
		$uploaddir = 'uploads/';
		if($curdir) $uploaddir .= $curdir . "/";
		$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);
		echo $uploadfile;
		echo '<pre>';
		if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
		    echo "File is valid, and was successfully uploaded to " . realpath($uploadfile);
		} else {
			switch ($_FILES['userfile'] ['error'])
			 {  case 1:
			           print '<p> The file is bigger than this PHP installation allows</p>';
			           break;
			    case 2:
			           print '<p> The file is bigger than this form allows</p>';
			           break;
			    case 3:
			           print '<p> Only part of the file was uploaded</p>';
			           break;
			    case 4:
			           print '<p> No file was uploaded</p>';
			           break;
			 }
		}
		
		echo 'Here is some more debugging info:';
		print_r($_FILES);
		
		print "</pre>";
		$this->_redirect("/admin/file/folderclear/$curdir");
	}
	
	public function newfolderAction()
	{
		$path = pathinfo($_SERVER["PHP_SELF"]);
		$newdir = realpath($_SERVER["DOCUMENT_ROOT"] . "/" . $path["dirname"] . "/uploads/") . $this->_getParam("curdir") . DIRECTORY_SEPARATOR . $this->_getParam("newdir");
		Zend_Debug::dump($newdir);
		mkdir($newdir);
		$redirect = str_replace("//", "/", "/admin/file/folder/" . $this->_getParam("curdir") . "/" . $this->_getParam("newdir"));
		$this->_redirect($redirect);
	}
	
	public function deleteAction()
	{
		$curdir = $this->_getParam("curdir");
		$files = $this->getRequest()->getParam("files");
		foreach($files as $key => $value)
		{
			$path = pathinfo($_SERVER["PHP_SELF"]);
			$file = realpath($_SERVER["DOCUMENT_ROOT"] . "/" . $path["dirname"] . "/uploads/" . $value);
			if(is_file($file)) unlink($file);
			if(is_dir($file)) $this->SureRemoveDir($file, true);
		}
		$this->_redirect("/admin/file/folder/$curdir");
	}
	
	function SureRemoveDir($dir, $DeleteMe) {
	    if(!$dh = @opendir($dir)) return;
	    while (false !== ($obj = readdir($dh))) {
	        if($obj=='.' || $obj=='..') continue;
	        if (!@unlink($dir.'/'.$obj)) $this->SureRemoveDir($dir.'/'.$obj, true);
	    }
	
	    closedir($dh);
	    if ($DeleteMe){
	        @rmdir($dir);
	    }
	}
	
	public function unzipAction()
	{
		$curdir = $this->_getParam("curdir");
		$param = $this->getRequest()->getParam("files");
		$files = new Files();
		$files->unzip($param, $curdir);		
		$this->_redirect("/admin/file/folder/$curdir");
	}
	
	public function zipAction()
	{
		$curdir = $this->_getParam("curdir");
		$param = $this->getRequest()->getParam("files");
		$files = new Files();
		$files->unzip($param, $curdir);		
		$this->_redirect("/admin/file/folder/$curdir");
	}
	
	public function resizeAction()
	{
//		$file = $this->getParam("curdir") . "/" . $this->getParam("image");
//		$path = pathinfo($_SERVER["PHP_SELF"]);
//		$file = realpath($_SERVER["DOCUMENT_ROOT"] . "/" . $path["dirname"] . "/uploads/" . $file);
//		if(is_file($file))
	}
	
	
}