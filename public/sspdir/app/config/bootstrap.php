<?php
/* SVN FILE: $Id: bootstrap.php 2951 2006-05-25 22:12:33Z phpnut $ */
/**
 * Short description for file.
 *
 * Long description for file
 *
 * PHP versions 4 and 5
 *
 * CakePHP :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright (c)	2006, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright (c) 2006, Cake Software Foundation, Inc.
 * @link				http://www.cakefoundation.org/projects/info/cakephp CakePHP Project
 * @package			cake
 * @subpackage		cake.app.config
 * @since			CakePHP v 0.10.8.2117
 * @version			$Revision: 2951 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2006-05-25 17:12:33 -0500 (Thu, 25 May 2006) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 *
 * This file is loaded automatically by the app/webroot/index.php file after the core bootstrap.php is loaded
 * This is an application wide file to load any function that is not used within a class define.
 * You can also use this to include or require any files in your application.
 *
 */
/**
 * The settings below can be used to set additional paths to models, views and controllers.
 * This is related to Ticket #470 (https://trac.cakephp.org/ticket/470)
 *
 * $modelPaths = array('full path to models', 'second full path to models', 'etc...');
 * $viewPaths = array('this path to views', 'second full path to views', 'etc...');
 * $controllerPaths = array('this path to controllers', 'second full path to controllers', 'etc...');
 *
 */
//EOF

// Set some Director vars
define('ALBUMS', ROOT . DS . 'albums');
define('AVATARS', ALBUMS . DS . 'avatars');
define('AUDIO', ROOT . DS . 'album-audio');
define('THUMBS', ROOT . DS . 'album-thumbs');
define('IMPORTS', ALBUMS . DS . 'imports');
define('DIR_HOST', 'http://' . preg_replace('/:80$/', '', env('HTTP_HOST')) . str_replace('/index.php?', '', BASE_URL));
define('DATA_LINK', DIR_HOST . '/images.php');
define('XML_CACHE', ROOT . DS . 'xml_cache');
define('THEMES', WWW_ROOT . 'styles');
define('USER_THEMES', ROOT . DS . 'themes');
define('DIR_VERSION', '1.1.9');
define('DIR_CACHE', 'director');
define('PLUGS', APP . 'director_plugins');
define('CUSTOM_PLUGS', ROOT . DS . 'plugins');

if (!defined('MAGICK_PATH')) {
	define('MAGICK_PATH_FINAL', 'convert');
} else if (strpos(strtolower(MAGICK_PATH), 'c:\\') !== false) {
	define('MAGICK_PATH_FINAL', '"' . MAGICK_PATH . '"');	
} else {
	define('MAGICK_PATH_FINAL', MAGICK_PATH);	
}

if (!defined('FORCE_GD')) {
	define('FORCE_GD', false);
}

// Bring in database configuration
if (@include_once(ROOT . DS . 'config' . DS . 'conf.php')) {
	define('DIR_DB_HOST', $host);
	define('DIR_DB_USER', $user);
	define('DIR_DB_PASSWORD', $pass);
	define('DIR_DB', $db);
	define('DIR_DB_PRE', $pre);
} else {
	// No config file, we need to redirect them to the install page
	if (preg_match('/install/', setUri()) || preg_match('/translate/', setUri())) {
		define('INSTALLING', true);
	} else {
		$url = DIR_HOST . '/index.php?/install';
		header("Location: $url");
	}
}

if (!defined('INSTALLING')) {
	define('INSTALLING', false);
}

include_once(ROOT . DS . 'app' . DS . 'vendors' . DS . 'bradleyboy' . DS . 'ensure.php');
include_once(ROOT . DS . 'app' . DS . 'vendors' . DS . 'director' . DS . 'salt.php');

function isVideo($fn) {
	if (eregi('\.flv|\.mov|\.mp4|\.m4a|\.m4v|\.3gp|\.3g2', $fn)) {
		return true;
	} else {
		return false;
	}
}

function isSwf($fn) {
	if (eregi('\.swf', $fn)) {
		return true;
	} else {
		return false;
	}
}

function isNotImg($fn) {
	if (isSwf($fn) || isVideo($fn)) {
		return true;
	} else {
		return false;
	}
}

?>