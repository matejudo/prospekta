<?php

vendor('asset_packager/asset_helper');

class AppController extends Controller {
	var $components = array('Director', 'RequestHandler', 'Kodak', 'Cookie', 'Pigeon');
	var $helpers = array('Asset', 'Director', 'Form');
	var $cookieKey = '7651029347yt0918h34t03';
	var $cookieName = 'DIRECTORDISTCOOKIE';
	
	////
	// Catch missing table for pre 1.0.6 installs
	////
	function appError($method, $params) {
		switch($method) {
			case 'missingTable':
				$go = '';
				$mia = $params[0]['table'];
				if (preg_match('/account/', $mia)) {
					header('Location: ' . DIR_HOST . '/index.php?/install' . $go);
					exit;
				} else {
					$this->webroot = str_replace('index.php?/', '', BASE_URL . '/app/webroot/');
					$this->viewPath = 'site';
					$this->render('db_error', 'simple');
					exit;
				}
				break;
		}
	}
	
	////
	// Session check
	////
    function checkSession() {
        if ($this->Session->check('User')) {
			$this->account = $this->Director->fetchAccount();
			if ($this->account['Account']['version'] != DIR_VERSION) {
				if (isset($this->account['Account']['lang'])) {
					$this->Session->write('Language', $this->account['Account']['lang']);
				}
				$this->redirect("/install/upgrade");
				exit;
			}
			if ((strtotime($this->account['Account']['last_check']) < time() || strtotime($this->account['Account']['last_check']) > strtotime('+1 week') || empty($this->account['Account']['activation_key'])) && $this->action != "activate" && !$this->Pigeon->isLocal()) {
				if ($this->Pigeon->activate($this->account['Account']['activation_key'], false)) {
					$this->redirect('/accounts/activate');
					exit;
				} else {
					loadModel('Account');
					$this->Account =& new Account();
					$this->Account->id = $this->account['Account']['id'];
					$this->data['Account']['last_check'] = date('Y-m-d H:i:s', strtotime('+1 week'));
					$this->Account->save($this->data);
				}
			}
			// As of 1.0.9, all users need an email address
			// TODO: Remove post 1.1
			$user = $this->Session->read('User');
			if (empty($user['email']) && !in_array($this->action, array('profile', 'update', 'activate'))) {
				$check = $this->Director->checkMail($user['id']);
				if (empty($check)) {
					$this->redirect("/users/profile");
					exit;
				}
			}
			$this->set('account', $this->account);
			Configure::write('Config.language', $this->account['Account']['lang']);
			$this->set('user', $this->Session->read('User'));
			define('CUR_USER_ID', $this->Session->read('User.id'));
			$this->set('shows', $this->Director->fetchShows($this->account['Account']['id']));
			$this->set('controller', $this);
			
			define('MAX_SIZE', $this->Director->returnBytes(ini_get('upload_max_filesize')));
			define('DIR_GD_VERSION', $this->Kodak->gdVersion());
		} else if ($this->Cookie->read('Login')) {
			loadModel('User');
			$this->User =& new User(); 
			$someone = $this->User->findByUsr($this->Cookie->read('Login'));
            if (!empty($someone['User']['pwd']) && md5($someone['User']['pwd']) == $this->Cookie->read('Pass')) {
	        	$this->Session->write('User', $someone['User']);
				$redirect_to = $this->Session->read('redirect_to');
				if (strrpos($redirect_to, '/') == (strlen($redirect_to)-1)) {
					$redirect_to = '';
				}
				if (empty($redirect_to)) {
					$location = $this->here;
				} else {
					$location = $redirect_to;
				}
				header("Location: $location");
	            exit;
			} else {
				// Force the user to login, record where they wanted to go
				if (!$this->Session->read('redirect_to')) {
					$this->Session->write('redirect_to', $this->here);
				}
	            $this->redirect("/users/login");
	            exit;
			}
		} else {
            // Force the user to login, record where they wanted to go
			if (!$this->Session->read('redirect_to')) {
				$this->Session->write('redirect_to', $this->here);
			}
			$here = explode('/index.php?', $this->here);
			$here = $here[count($here)-1];
			if ($here == '/' || $here == '/snapshot') {
            	$this->redirect("/users/login");
			} else {
				$this->redirect("/snapshot");
			}
            exit;
        }
    }

	////
	// Make sure ajax calls are actual ajax calls
	////
	function verifyAjax() {
		if (!$this->RequestHandler->isAjax()) {
			$this->redirect("/");
			exit;
		}
	}
	
	function verifyRight($level) {
		if ($this->Session->read('User.perms') >= $level) {
			return true;
		} else {
			$this->redirect("/");
            exit;
		}
	}
}

?>