<?php

class InstallController extends AppController
{
    var $name = 'Install';
	var $uses = array();
	var $helpers = array('Html', 'Javascript', 'Director');
	var $components = array('Director', 'Pigeon', 'Cookie', 'Session');

	function beforeFilter() {
		if ($this->Session->read('Language')) {
			Configure::write('Config.language', $this->Session->read('Language'));
		}
		$this->pageTitle = __("Installing", true);
		$this->set('config_path', ROOT . DS . 'config');
		$this->set('controller', $this);
		$this->layout = 'simple';
	}

	////
	// Install landing page
	////
	function index() {
		$lang_folder = new Folder(ROOT . DS . 'locale');
		$langs = $lang_folder->ls(true, false);
		$actual = array();
		foreach ($langs[0] as $l) {
			if (($l != 'eng' && $l != 'SAMPLE') && file_exists(ROOT . DS . 'locale' . DS . $l . DS . 'welcome.po')) {
				$actual[] = $l;
			}
		}
		if (empty($actual)) {
			$this->Session->write('Language', 'eng');
			$this->redirect('/install/license');
			exit;
		}
		$this->set('langs', $actual);
	}

	function lang($l) {
		$this->Session->write('Language', $l);
		$this->redirect('/install/license');
		exit;
	}

	////
	// Install license
	////
	function license() {}

	////
	// Activation
	////
	function activate() {
		$this->Session->write('activation', null);
	}

	////
	// Perform server check
	////
	function test() {
		if ($this->data) {
			$php = version_compare(PHP_VERSION, '4.3.7', 'ge');
			extension_loaded('mysql') ? $mysql = true : $mysql = extension_loaded('mysqli');
			if (ini_get('safe_mode') == false || ini_get('safe_mode') == '' || strtolower(ini_get('safe_mode')) == 'off') {
				$no_safe_mode = true;
			} else {
				$no_safe_mode = false;
			}
			if ($php && $mysql && $no_safe_mode) {
				$this->set('success', true);
			} else {
				$this->set('success', false);
				$this->set('php', $php);
				$this->set('mysql', $mysql);
				$this->set('no_safe_mode', $no_safe_mode);
			}
		} else {
			$this->redirect('/install');
			exit;
		}
	}

	////
	// Enter database details and create config file
	////
	function database() {
		$this->set('db_select_error', false);
		$this->set('connection_error', false);
		$this->set('conf_exists', false);
		$filename = ROOT . DS . 'config' . DS . 'conf.php';

		if ($this->data) {
			if (file_exists($filename)) {
				$this->set('conf_exists', true);
			} else {
				$details = $this->data['db'];
				$server = trim($details['server']);
				$name = trim($details['name']);
				$user = trim($details['user']);
				$pass = trim($details['pass']);
				$prefix = trim($details['prefix']);

				if (strpos($server, ':') !== false) {
					$bits = explode(':', $server);
					$server = $bits[0];
					$extra = "ini_set('mysql.default_socket', '{$bits[1]}');";
					ini_set('mysql.default_socket', $bits[1]);
				}

				$link = @mysql_connect($server, $user, $pass);
				if (!$link) {
				    $this->set('connection_error', true);
					$this->set('mysql_error', mysql_error());
				} elseif (@!mysql_select_db($details['name'])) {
					$this->set('db_select_error', true);
					$this->set('mysql_error', mysql_error());
				} else {
					$fill = "<?php\n\n\t";
					$fill .= '$host = \''.	$server	."';\n\t";
					$fill .= '$db = \''.	$name	."';\n\t";
					$fill .= '$user = \''.	$user	."';\n\t";
					$fill .= '$pass = \''.	$pass	."';\n\n\t";
					$fill .= '$pre = \''.	$prefix	."';\n\n";
					if (isset($extra)) {
						$fill .= "\t$extra\n\n";
					}
					$fill .= '?>';

					$handle = fopen($filename, 'w+');

					if (fwrite($handle, $fill) == false) {
						$this->set('write_error', true);
					} else {
						$this->redirect('/install/register');
						exit;
					}
				}
			}
		} else {
			if (file_exists($filename)) {
				$this->set('conf_exists', true);
			}
		}
	}

	////
	// Create the first user
	////
	function register() {}

	////
	// Install it already!
	////
	function finish() {
		if ($this->data) {
			mysql_connect(DIR_DB_HOST, DIR_DB_USER, DIR_DB_PASSWORD);
			mysql_select_db(DIR_DB);

			$atbl = DIR_DB_PRE . 'albums';
			$itbl = DIR_DB_PRE . 'images';
			$dtbl = DIR_DB_PRE . 'dynamic';
			$dltbl = DIR_DB_PRE . 'dynamic_links';
			$stbl = DIR_DB_PRE . 'slideshows';
			$utbl = DIR_DB_PRE . 'usrs';
			$acctbl = DIR_DB_PRE . 'account';

			$this->set('error', '');

			$queries = array(
					"CREATE TABLE $atbl(id INT AUTO_INCREMENT, PRIMARY KEY(id), name VARCHAR(100), description BLOB, path VARCHAR(50), tn TINYINT(1) NOT NULL DEFAULT '0', aTn VARCHAR(150), active TINYINT(1) NOT NULL DEFAULT '0', audioFile VARCHAR(100) DEFAULT NULL, audioCap VARCHAR(200) DEFAULT NULL, displayOrder INT(4) DEFAULT '999', target TINYINT(1) NOT NULL DEFAULT '0', images_count INT NOT NULL DEFAULT 0, sort_type VARCHAR(255) NOT NULL DEFAULT 'manual', title_template VARCHAR(255), link_template TEXT, caption_template TEXT, modified DATETIME DEFAULT NULL, created DATETIME DEFAULT NULL, updated_by INT(11), created_by INT(11))",
					"CREATE TABLE $itbl(id INT AUTO_INCREMENT, PRIMARY KEY(id), aid INT, title VARCHAR(255), src VARCHAR(255), caption TEXT, link TEXT, active TINYINT(1) NOT NULL DEFAULT '1', seq INT(4) NOT NULL DEFAULT '999', pause INT(4) NOT NULL DEFAULT '0', target TINYINT(1) NOT NULL DEFAULT '0', modified DATETIME DEFAULT NULL, created DATETIME DEFAULT NULL, updated_by INT(11), created_by INT(11), anchor VARCHAR(255) DEFAULT NULL)",
					"CREATE TABLE $utbl(id INT AUTO_INCREMENT, PRIMARY KEY(id), usr VARCHAR(50), pwd VARCHAR(50), email VARCHAR(255), perms INT(2) NOT NULL DEFAULT '1', modified DATETIME DEFAULT NULL, created DATETIME DEFAULT NULL, news TINYINT(1) DEFAULT 0, help TINYINT(1) DEFAULT 0)",
					"CREATE TABLE $dtbl(id INT AUTO_INCREMENT, PRIMARY KEY(id), name VARCHAR(100), description TEXT, modified DATETIME DEFAULT NULL, created DATETIME DEFAULT NULL, main TINYINT(1) DEFAULT 0, sort_type VARCHAR(255) NOT NULL DEFAULT 'manual', updated_by INT(11), created_by INT(11))",
					"CREATE TABLE $dltbl(id INT AUTO_INCREMENT, PRIMARY KEY(id), did INT, aid INT, display INT DEFAULT '800')",
					"CREATE TABLE $stbl(id INT AUTO_INCREMENT, PRIMARY KEY(id), name VARCHAR(255), url VARCHAR(255))",
					"INSERT INTO $utbl (id, usr, email, pwd, perms, created, modified) VALUES (NULL, '" . $this->data['User']['usr'] . "', '" . $this->data['User']['email'] . "', '" . $this->data['User']['pwd'] . "', 4, NOW(), NOW())",
					"CREATE TABLE $acctbl(id INT AUTO_INCREMENT, PRIMARY KEY(id), externals TINYINT(1), internals TINYINT(1), version VARCHAR(255), activation_key VARCHAR(255), last_check DATETIME, theme VARCHAR(255) DEFAULT '/app/webroot/styles/default/default.css', lang VARCHAR(255) DEFAULT 'eng')",
					"INSERT INTO $acctbl (id, externals, internals, version, activation_key, last_check, lang) VALUES (NULL, 1, 1, '" . DIR_VERSION . "', '" . $this->Session->read('activation') . "', '" . date('Y-m-d H:i:s', strtotime('+2 weeks')) . "', NULL)",
					"INSERT INTO $dtbl(name, description, main, created, modified) VALUES('All albums', 'This gallery contains all published albums.', 1, NOW(), NOW())"
				);

			foreach($queries as $query) {
				if (!mysql_query($query)) {
					$this->set('error', mysql_error());
					$this->render();
					exit;
				}
			}
			$this->_clean(CACHE . DS . 'models');
		} else {
			$this->redirect('/install');
		}
	}

	////
	// Perform upgrade
	////
	function upgrade($step = 1) {
		define('CUR_USER_ID', $this->Session->read('User.id'));

		// Make sure they have the appropriate version of PHP, as 1.0.8+ now requires 4.3.2+
		if (version_compare(PHP_VERSION, '4.3.7', '>=')) {

			if (function_exists('set_time_limit')) {
				set_time_limit(0);
			}
			$this->set('error', false);
			$this->set('step', $step);

			if ($step != 1) {
				mysql_connect(DIR_DB_HOST, DIR_DB_USER, DIR_DB_PASSWORD);
				mysql_select_db(DIR_DB);

				$version = DIR_VERSION;
				$atbl = DIR_DB_PRE . 'albums';
				$itbl = DIR_DB_PRE . 'images';
				$dtbl = DIR_DB_PRE . 'dynamic';
				$dltbl = DIR_DB_PRE . 'dynamic_links';
				$stbl = DIR_DB_PRE . 'slideshows';
				$utbl = DIR_DB_PRE . 'usrs';
				$acctbl = DIR_DB_PRE . 'account';
			}

			switch($step) {
				case(2):
					@mysql_query("CREATE TABLE $acctbl(id INT AUTO_INCREMENT, PRIMARY KEY(id), externals TINYINT(1), internals TINYINT(1), process_specs VARCHAR(255), thumb_specs VARCHAR(255), version VARCHAR(255)) ");

					$check = mysql_query("SELECT * FROM $acctbl");

					if (mysql_num_rows($check) == 0):
						@mysql_query("INSERT INTO $acctbl VALUES (NULL, 1, 1, '', '', '$version')");
					endif;

					// 1.0.0
					@mysql_query("ALTER TABLE $atbl CHANGE displayOrder displayOrder INT(4) NOT NULL DEFAULT '999'");
					@mysql_query("ALTER TABLE $itbl CHANGE seq seq INT(4) NOT NULL DEFAULT '999'");
					@mysql_query("ALTER TABLE $itbl ADD pause INT(4) NOT NULL DEFAULT '0'");
					@mysql_query("ALTER TABLE $itbl ADD title VARCHAR(255)");
					@mysql_query("ALTER TABLE $itbl ADD target TINYINT(1) NOT NULL DEFAULT '0'");
					@mysql_query("ALTER TABLE $utbl ADD perms TINYINT(1) NOT NULL DEFAULT '1'");
					@mysql_query("CREATE TABLE $stbl(id INT AUTO_INCREMENT, PRIMARY KEY(id), name VARCHAR(255), url VARCHAR(255))");
					@mysql_query("UPDATE $utbl SET perms = 4 WHERE id = {$_SESSION['loginID']}");

					// 1.0.3
					@mysql_query("ALTER TABLE $atbl ADD show_headers INT(1) NOT NULL DEFAULT '1'");
					@mysql_query("ALTER TABLE $atbl ADD process_specs VARCHAR(255)");
					@mysql_query("ALTER TABLE $atbl ADD thumb_specs VARCHAR(255)");
					@mysql_query("UPDATE $atbl SET show_headers = 1");
					@mysql_query("ALTER TABLE $itbl CHANGE src src VARCHAR(255)");

					// 1.0.5
					@mysql_query("UPDATE $atbl SET thumb_specs = CONCAT(thumb_specs, 'x0') WHERE thumb_specs IS NOT NULL AND thumb_specs <> ''");

					// 1.0.6
					@mysql_query("UPDATE $atbl SET process_specs = CONCAT(process_specs, 'x0') WHERE thumb_specs IS NOT NULL AND thumb_specs <> ''");

					// 1.0.7
					@mysql_query("ALTER TABLE $atbl ADD updated_on TIMESTAMP");
					@mysql_query("ALTER TABLE $atbl ADD created_on TIMESTAMP");

					@mysql_query("UPDATE $atbl SET created_on = NOW() WHERE created_on IS NULL OR created_on = '' OR created_on = '0000-00-00 00:00'");

					@mysql_query("ALTER TABLE $itbl ADD updated_on TIMESTAMP");
					@mysql_query("ALTER TABLE $itbl ADD created_on TIMESTAMP");

					@mysql_query("UPDATE $itbl SET created_on = NOW() WHERE created_on IS NULL OR created_on = '' or created_on = '0000-00-00 00:00'");

					// 1.0.8
					@mysql_query("ALTER TABLE $utbl CHANGE perms perms INT(2) NOT NULL DEFAULT 1");
					@mysql_query("ALTER TABLE $atbl ADD images_count INT NOT NULL DEFAULT 0");
					@mysql_query("ALTER TABLE $atbl ADD sort_type VARCHAR(255) NOT NULL DEFAULT 'manual'");

					// 1.0.9
					@mysql_query("ALTER TABLE $atbl ADD title_template VARCHAR(255)");
					@mysql_query("ALTER TABLE $atbl ADD link_template INT(2)");
					@mysql_query("ALTER TABLE $atbl ADD caption_template TEXT");
					@mysql_query("ALTER TABLE $utbl ADD email VARCHAR(255)");

					// 1.1
					@mysql_query("ALTER TABLE $dtbl ADD description TEXT");
					@mysql_query("ALTER TABLE $dtbl ADD modified TIMESTAMP");
					@mysql_query("ALTER TABLE $dtbl ADD created TIMESTAMP");
					@mysql_query("UPDATE $dtbl SET modified = NOW(), created = NOW() WHERE created_on IS NULL OR created_on = '' or created_on = '0000-00-00 00:00'");
					@mysql_query("ALTER TABLE $dtbl ADD main TINYINT(1) DEFAULT 0");
					@mysql_query("UPDATE $dtbl SET `main` = 0 WHERE `main` <> 1");
					$test = mysql_query("SELECT * FROM $dtbl WHERE `main` = 1");
					if (mysql_num_rows($test) != 1) {
				    	@mysql_query("INSERT INTO $dtbl(name, description, main, created, modified) VALUES('All albums', 'This gallery contains all published albums.', 1, NOW(), NOW())");
						$new_id = mysql_insert_id();
						$inner_r = mysql_query("SELECT id, displayOrder FROM $atbl WHERE active = 1");
						if (mysql_num_rows($inner_r) > 0) {
							while ($r = mysql_fetch_array($inner_r)) {
								mysql_query("INSERT INTO $dltbl(did, aid, display) VALUES($new_id, {$r['id']}, {$r['displayOrder']})");
							}
						}
					}

					@mysql_query("ALTER TABLE $atbl ADD created_by INT(11)");
					@mysql_query("ALTER TABLE $atbl ADD updated_by INT(11)");
					@mysql_query("ALTER TABLE $itbl ADD created_by INT(11)");
					@mysql_query("ALTER TABLE $itbl ADD updated_by INT(11)");
					@mysql_query("ALTER TABLE $dtbl ADD created_by INT(11)");
					@mysql_query("ALTER TABLE $dtbl ADD updated_by INT(11)");

					$res = mysql_query("SELECT * FROM $utbl ORDER BY perms DESC LIMIT 1");
					if (mysql_num_rows($res) != 0) {
						$u = mysql_fetch_row($res); $id = $u[0];
						@mysql_query("UPDATE $atbl SET created_by = $id, updated_by = $id WHERE created_by IS NULL");
						@mysql_query("UPDATE $dtbl SET created_by = $id, updated_by = $id WHERE created_by IS NULL");
						@mysql_query("UPDATE $itbl SET created_by = $id, updated_by = $id WHERE created_by IS NULL");
					}

					@mysql_query("ALTER TABLE $atbl CHANGE created_on created TIMESTAMP");
					@mysql_query("ALTER TABLE $itbl CHANGE created_on created TIMESTAMP");
					@mysql_query("ALTER TABLE $dtbl CHANGE created_on created TIMESTAMP");
					@mysql_query("ALTER TABLE $atbl CHANGE updated_on modified TIMESTAMP");
					@mysql_query("ALTER TABLE $itbl CHANGE updated_on modified TIMESTAMP");
					@mysql_query("ALTER TABLE $dtbl CHANGE updated_on modified TIMESTAMP");

					@mysql_query("ALTER TABLE $atbl DROP created_on");
					@mysql_query("ALTER TABLE $itbl DROP created_on");
					@mysql_query("ALTER TABLE $dtbl DROP created_on");
					@mysql_query("ALTER TABLE $atbl DROP updated_on");
					@mysql_query("ALTER TABLE $itbl DROP updated_on");
					@mysql_query("ALTER TABLE $dtbl DROP updated_on");

					@mysql_query("ALTER TABLE $dtbl CHANGE main main TINYINT(1) DEFAULT 0");
					@mysql_query("ALTER TABLE $acctbl ADD activation_key VARCHAR(255)");
					@mysql_query("ALTER TABLE $acctbl ADD last_check DATETIME");
					if (@mysql_query("ALTER TABLE $acctbl ADD theme VARCHAR(255) default '/app/webroot/styles/default/default.css'")) {
						@mysql_query("UPDATE $acctbl SET theme = '/app/webroot/styles/default/default.css' WHERE theme IS NULL or theme =''");
					}
					if (@mysql_query("ALTER TABLE $acctbl ADD lang VARCHAR(255) default 'eng'")) {
						@mysql_query("UPDATE $acctbl SET lang = 'eng'");
					}
					@mysql_query("UPDATE $acctbl SET lang = 'eng' WHERE lang = 'en'");

					// New link templates
					@mysql_query("ALTER TABLE $atbl CHANGE link_template link_template TEXT");

					$full = DIR_HOST . '/popup.php?src=[full_hr_url]&w=[img_w]&h=[img_h]&title=[img_src]';
					$template = mysql_real_escape_string("javascript:if (window.NewWindow) { NewWindow.close(); }; NewWindow=window.open('$full','myWindow','width=[img_w],height=[img_h],toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,titlebar=no');NewWindow.focus(); void(0);");

					@mysql_query("UPDATE $atbl SET link_template = '{$template}__~~__0' WHERE link_template = '3'");

					$template = '[full_hr_url]';
					@mysql_query("UPDATE $atbl SET link_template = '{$template}__~~__1' WHERE link_template = '2'");
					@mysql_query("UPDATE $atbl SET link_template = '{$template}__~~__0' WHERE link_template = '1'");

					@mysql_query("UPDATE $itbl SET link = REPLACE(link, '/hr/', '/lg/')");

					if (@mysql_query("ALTER TABLE $dtbl ADD sort_type VARCHAR(255) NOT NULL DEFAULT 'manual'")) {
						@mysql_query("UPDATE $atbl SET sort_type = 'manual");
					}

					if (@mysql_query("ALTER TABLE $utbl ADD created TIMESTAMP")) {
						@mysql_query("ALTER TABLE $utbl ADD modified TIMESTAMP");
						$res = mysql_query("SELECT created FROM $atbl ORDER BY created LIMIT 1");
						if (mysql_num_rows($res) == 1) {
							$u = mysql_fetch_row($res);
							@mysql_query("UPDATE $utbl SET created = '$u[0]', modified = '$u[0]'");
						} else {
							@mysql_query("UPDATE $utbl SET created = NOW(), modified = NOW()");
						}
					}

					@mysql_query("ALTER TABLE $atbl CHANGE created created DATETIME DEFAULT NULL");
					@mysql_query("ALTER TABLE $itbl CHANGE created created DATETIME DEFAULT NULL");
					@mysql_query("ALTER TABLE $dtbl CHANGE created created DATETIME DEFAULT NULL");
					@mysql_query("ALTER TABLE $atbl CHANGE modified modified DATETIME DEFAULT NULL");
					@mysql_query("ALTER TABLE $itbl CHANGE modified modified DATETIME DEFAULT NULL");
					@mysql_query("ALTER TABLE $dtbl CHANGE modified modified DATETIME DEFAULT NULL");
					@mysql_query("ALTER TABLE $utbl CHANGE created created DATETIME DEFAULT NULL");
					@mysql_query("ALTER TABLE $utbl CHANGE modified modified DATETIME DEFAULT NULL");

					@mysql_query("ALTER TABLE $utbl DROP avatar");
					@mysql_query("ALTER TABLE $atbl DROP process_specs");
					@mysql_query("ALTER TABLE $atbl DROP thumb_specs");
					@mysql_query("ALTER TABLE $acctbl DROP process_specs");
					@mysql_query("ALTER TABLE $acctbl DROP thumb_specs");

					@mysql_query("ALTER TABLE $utbl ADD news TINYINT(1) DEFAULT 1");
					@mysql_query("ALTER TABLE $utbl ADD help TINYINT(1) DEFAULT 1");
					@mysql_query("UPDATE $utbl SET news = 1, help = 1 WHERE news IS NULL");
					if (@mysql_query("ALTER TABLE $itbl ADD anchor VARCHAR(255) DEFAULT NULL")) {
						$targets = glob(ALBUMS . DS . '*' . DS . 'cache' . DS . '*');
						foreach ($targets as $t) {
							@unlink($t);
						}
					}

					@mysql_query("DELETE FROM $dtbl WHERE name = '' OR name IS NULL");
					@mysql_query("DELETE FROM $itbl WHERE aid IS NULL");
					break;

				case(3):
					// Move stuff around
					$targets = glob(ALBUMS . DS . '*');
					foreach ($targets as $t) {
						if (basename($t) != 'imports' && basename($t) != 'avatars') {
							$hr = $t . DS . 'hr';
							$lg = $t . DS . 'lg';
							$tn = $t . DS . 'tn';
							$dir = $t . DS . 'director';
							$cache = $t . DS . 'cache';
							if (is_dir($t . DS . 'hr')) {
								foreach (glob($hr . DS . '*') as $h) {
									rename($h, $lg . DS . basename($h));
								}
								$this->Director->rmdirr($hr);
							}
							$this->Director->rmdirr($tn);
							$this->Director->rmdirr($dir);
							new Folder($cache, true);
						}
					}

					// Custom thumbs
					$targets = glob(THUMBS . DS . 'album-*');
					if (!empty($targets)) {
						loadModel('Image');
						$this->Image =& new Image();
						foreach ($targets as $t) {
							$file = basename($t);
							preg_match('/^album-([0-9]+)\..*/', $file, $matches);
							$id = $matches[1];
							$album = $this->Image->Album->read(null, $id);
							rename($t, ALBUMS . DS . $album['Album']['path'] . DS . 'lg' . DS . $file);
							$this->Image->create();
							$data['Image']['aid'] = $id;
							$data['Image']['src'] = $file;
							$data['Image']['created_by'] = $data['Image']['updated_by'] = CUR_USER_ID;
							$data['Image']['active'] = 0;
							$this->Image->save($data);
						}
					}
					$this->Director->rmdirr(THUMBS);

					// Clear cache if not upgraded to 1.1.4 yet
					$check = mysql_fetch_row(mysql_query("SELECT version FROM $acctbl LIMIT 1"));
					$version = $check[0];
					if (strlen($version) == 3) {
						$version .= '.0';
					}
					if (version_compare($version, '1.1.4', '<')) {
						$clear = glob(ALBUMS . DS . '*' . DS . 'cache' . DS . '*');
						if (!empty($clear)) {
							foreach($clear as $crusty) {
								@unlink($crusty);
							}
						}
					}
					break;

				case(4):
					// Strip abs path from album thumbs
					$results = mysql_query("SELECT * FROM $atbl WHERE aTn <> '' AND aTn IS NOT NULL");
					while ($r = mysql_fetch_array($results)) {
						$tn = basename($r['aTn']);
						if (strpos($r['aTn'], '/') !== false) {
							mysql_query("UPDATE $atbl SET aTn = '$tn' WHERE id = {$r['id']}");
						}
					}

					// Clean XML cache and model cache
					$this->_clean(XML_CACHE);
					$this->_clean(CACHE . DS . 'models');
					$this->_clean(CACHE . DS . 'director');

					// Create avatars folder if it does not exist
					uses('Folder');
					new Folder(AVATARS, true);

					// Avatar cleanup
					$avs = glob(AVATARS . DS . '*');
					if (!empty($avs)) {
						foreach($avs as $a) {
							$file = basename($a);
							if (strpos($file, '.') === false) {
								$specs = getimagesize($a);
								switch(strtolower($specs['mime'])) {
									case 'image/jpeg':
										$new = $file . '.jpg';
										break;
									case 'image/gif':
										$new = $file . '.gif';
										break;
									default:
										$new = $file . '.png';
										break;
								}
								rename($a, AVATARS . DS . $new);
							}
						}
					}
					$this->Cookie->del('Login');
					$this->Cookie->del('Pass');
			        $this->Session->delete('User');

					@mysql_query("UPDATE $acctbl SET version = '$version'");
					break;
			}
	 	} else {
			$this->set('error', true);
		}
	}

	////
	// Clean a directory
	////
	function _clean($dir) {
		if ($dh = @opendir($dir)) {
			while (($obj = readdir($dh))) {
		       if ($obj=='.' || $obj=='..' || $obj =='.svn') continue;
		       if (!@unlink($dir.'/'.$obj)) $this->Director->rmdirr($dir.'/'.$obj);
		   	}
		}
	}
}