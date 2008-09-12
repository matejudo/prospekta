<?php

class AccountsController extends AppController {
    var $name = 'Accounts';
	var $helpers = array('Html', 'Javascript', 'Ajax');

	var $non_ajax_actions = array('preferences', '_data', 'theme', 'language', 'activate');

	// Only logged in users should see this controller's actions
 	function beforeFilter() {
		// Protect ajax actions
		if (!in_array($this->action, $this->non_ajax_actions)) {
			$this->verifyAjax();
		}
		// Check session
		$this->checkSession();
		$this->verifyRight(3);
	}

	////
	// Manage account preferences
	////
	function preferences() {
		$this->pageTitle = __('Preferences', true);
		$account = $this->Account->find();
		$this->set('account', $account);
		DIR_GD_VERSION > 0 ? $gd = true : $gd = false;
		$this->set('gd', $gd);
		$this->set('curl', extension_loaded('curl'));
		uses('Folder');
		$themes_folder = new Folder(THEMES);
		$themes = $themes_folder->ls(true, false);
		$user_themes_folder = new Folder(USER_THEMES);
		$user_themes = $user_themes_folder->ls(true, false);
		$this->set('themes', $themes[0]);
		$this->set('user_themes', $user_themes[0]);
		$lang_folder = new Folder(ROOT . DS . 'locale');
		$langs = $lang_folder->ls(true, false);
		$this->set('langs', $langs[0]);

		if (DIR_GD_VERSION >= 3) {
			$image_lib = 'ImageMagick';
		} elseif (DIR_GD_VERSION == 2) {
			$image_lib = 'GD2';
		} else {
			$image_lib = 'GD1';
		}

		$info = array(
					'php' => phpversion(),
					'memory' => ini_get('memory_limit'),
					'processing' => $image_lib,
					'max_upload' => ini_get('upload_max_filesize'),
					'post_max' => ini_get('post_max_size')
				);

		$this->set('info', $info);
	}

	////
	// Update accoumt
	////
	function update($id) {
		$this->Account->id = $id;
		$this->Account->save($this->data);
		exit;
	}

	function theme($new_theme) {
		$account = $this->account;
		$this->Account->id = $account['Account']['id'];
		$theme = r('--', '/', strtolower($new_theme));
		$this->Account->saveField('theme', '/' . $theme . '.css');
		$this->redirect('/accounts/preferences');
	}

	function language($new_lang) {
		$account = $this->account;
		$this->Account->id = $account['Account']['id'];
		$this->Account->saveField('lang', $new_lang);
		Configure::write('Config.language', $new_lang);
		loadModel('User');
		$this->User =& new User();
		$u = $this->User->find('id=' . CUR_USER_ID);
		$this->Session->write('User', $u['User']);
		$this->redirect('/accounts/preferences');
	}

	function activate() {
		$this->layout = "simple";
	}
}

?>