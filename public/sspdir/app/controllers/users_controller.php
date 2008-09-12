<?php

class UsersController extends AppController
{
    var $name = 'Users';
	var $uses = array('User'); 
	var $helpers = array('Html', 'Javascript', 'Ajax');
	var $components = array('Cookie');
	
	// No session check for these actions
	var $no_check = array('login', 'password', 'send_password', 'avatar');
	// Which actions should have simple layout
	var $simpletons = array('login', 'password', 'send_password', 'logout');
	// Verify that any action *not* in this list is an Ajax call
	var $non_ajax_actions = array('manage', '_list', 'profile', 'login', 'logout', 'password', 'avatar', 'clear_avatar');

	// Only logged in users should see this controller's actions
 	function beforeFilter() {
		// Protect ajax actions
		if (!in_array($this->action, $this->non_ajax_actions)) {
			$this->verifyAjax();
		}
		
		// Check session
		if (!in_array($this->action, $this->no_check)) {
			$this->checkSession();
		}
		
		$this->set('controller', $this);
		
		if (in_array($this->action, $this->simpletons)) {
			$this->layout = 'simple';
			$this->account = $this->Director->fetchAccount();
			$this->set('account', $this->account);
			Configure::write('Config.language', $this->account['Account']['lang']);
		}
	}
	
	////
	// Acts as both the login display and actual login ui
	////
	function login() {
		$this->pageTitle = __('Login', true);

        if (!empty($this->data)) {
            $someone = $this->User->findByUsr($this->data['User']['usr']);
            if (!empty($someone['User']['pwd']) && $someone['User']['pwd'] == $this->data['User']['pwd']) {
				$this->Session->write('User', $someone['User']);
				if ($this->data['remember']) {
					$cookie['user'] = $someone['User']['usr'];
					$cookie['pwd'] = md5($someone['User']['pwd']);
					$this->Cookie->write('Login', $cookie['user'], true, 60*60*24*200);
					$this->Cookie->write('Pass', $cookie['pwd'], true, 60*60*24*200);
				}
				$redirect_to = $this->Session->read('redirect_to');
				if (strrpos($redirect_to, '/') == (strlen($redirect_to)-1)) {
					$redirect_to = '';
				}
				if (empty($redirect_to)) {
                	$this->redirect('/snapshot');
				} else {
					$this->Session->delete('redirect_to');
					header("Location: $redirect_to");
					exit();
				}
            } else {
                $this->set('error', __('Login incorrect', true));
            }
        }
    }

	////
	// Password/login retrieval
	////
	function password() {
		$this->pageTitle = __('Password Reminder', true);
	}
	
	////
	// Password/login retrieval action
	////
	function send_password() {
		if ($this->data) {
			$cred = $this->data['User']['cred'];
			$user = $this->User->find("usr = '$cred' OR email = '$cred'");
			if (empty($user)) {
				$success = 0;
			} else {
				if (empty($user['User']['email'])) {
					$success = 1;
				} else {
					$path = DIR_HOST;
					$email = $user['User']['email'];
					$usr = $user['User']['usr'];
					$pwd = $user['User']['pwd'];
				
					$message = __('This is a password reminder sent from SlideShowPro Director.',  true) . "\n\n------------------------------\n\n";
					$message .= __('Login here', true) . ': ' . $path . "/\n";
					$message .= __('Username', true) . ": $usr\n";
					$message .= __('Password', true) . ": $pwd\n\n";

					$headers = 'From: ' . $email . "\n";
					$headers .= "Content-Type: text/plain;charset=UTF-8";

					$subject = __('SlideShowPro Director Login Reminder', true);
					
					if (function_exists('mb_convert_encoding')) {
						$subject = mb_convert_encoding($subject, "ISO-8859-1", "UTF-8");
						$subject = mb_encode_mimeheader($subject);
					}
					
					if (mail($email, $subject, $message, $headers)) {
						$success = 3;
					} else {
						$success = 2;
					}
				}
			}
			$this->set('success', $success);
			$this->render('send_password', 'ajax');
	 	} else {
			exit;
		}
	}
	
	////
	// Log the user out
	////
	function logout() {	
		$this->pageTitle = __('Logout', true);
		$this->Cookie->del('Login');
		$this->Cookie->del('Pass');
        $this->Session->delete('User');
    }

	////
	// Edit user profile
	////
	function profile() {
		$this->pageTitle = __('User Profile', true);
		$id = $this->Session->read('User.id');
		$this->data = $this->User->read(null, $id);
	}
	
	
	function clear_avatar() {
		$avs = glob(AVATARS . DS . CUR_USER_ID . '.*');
		foreach($avs as $a) {
			unlink($a);
		}
		$this->redirect('/users/profile');
		exit;
	}
	
	////
	// Update a user record
	////
	function update($id) {
		$this->User->id = $id;
		if (!empty($this->params['form']['pass1'])) {
			$this->data['User']['pwd'] = $this->params['form']['pass1'];
		}
		$this->User->save($this->data);
		if (!isset($this->data['Perms'])) {
			$u = $this->User->find(aa('id', $id));
			$this->Session->write('User', $u['User']);
		}
	}
	
	function update_options() {
		$this->User->id = $id = $this->data['User']['id'];
		$this->User->save($this->data);
		$u = $this->User->find(aa('id', $id));
		$this->Session->write('User', $u['User']);
		exit;
	}
	
	////
	// Delete a user
	////
	function delete() {
		$this->User->del($this->data['User']['id']);
		$this->_list();
		$this->render('users', 'ajax');
	}
	
	////
	// Create user
	////
	function create() {
		if (!empty($this->data['User'])) {
			$this->data['User']['email'] = $this->data['User']['usr'];
			if ($this->User->save($this->data['User'])) {
				$this->User->id = $this->User->getLastInsertId();
				$pwd = $this->Director->randomStr();
				$this->User->saveField('pwd', $pwd);
				$path = DIR_HOST;
				$email = $this->data['User']['usr'];
				
				$message = $this->params['form']['message'];
				$message .= "\n\n------------------------------\n\n";
				$message .= __('Login here', true) . ': ' . $path . "/\n";
				$message .= __('Username', true) . ": $email\n";
				$message .= __('Password', true) . ": $pwd\n\n";
				$message .= __('Once you login you can change your password to something more familiar.', true);

				$headers = __('From', true) . ': ' . $this->params['form']['from_email'] . "\n";
				$headers .= "Content-Type: text/plain;charset=UTF-8";
				
				$subject = __('SlideShowPro Director Login', true);
				
				if (function_exists('mb_convert_encoding')) {
					$subject = mb_convert_encoding($subject, "ISO-8859-1", "UTF-8");
					$subject = mb_encode_mimeheader($subject);
				}
				
				mail($email, $subject, $message, $headers);
				$this->_list();
				$this->render('users', 'ajax');
			}
		}
	}
	
	////
	// Manage users
	////
	function manage() {
		$this->verifyRight(3);
		$this->pageTitle = __('Users', true);
		$this->_list();
	}
	
	////
	// Get list of users for manage actions
	////
	function _list() {
		$this->set('users', $this->User->findAll("perms <> 4", null, 'usr'));
	}
}

?>