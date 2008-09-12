<?php
	
	error_reporting(0);
	
	$val = $_GET['a'];
	if (strpos($val, 'http://') !== false) {
		header('Location: ' . $val);
		exit;
	} else {
		$val = str_replace(' ', '%2B', $val);
	}
	
	function clean($var) {
		if (is_numeric($var)) {
			return $var;
		} else {
			die('nono');
			exit;
		}
	}
	
	function returnExt($file) {
		$pos = strrpos($file, '.');
		return strtolower(substr($file, $pos+1, strlen($file)));
	}
	
	define('ROOT', dirname(dirname(dirname(__FILE__))));
	define('DS', DIRECTORY_SEPARATOR);
	
	@include(ROOT . DS . 'config' . DS . 'user_setup.php');
	include_once(ROOT . DS . 'app' . DS . 'vendors' . DS . 'director' . DS . 'salt.php');
	
	$crypt = convert($val, false);

	$a = explode(',', $crypt);
	$file = $fn = $a[0];
	$path = $a[1];
	$w = $a[2];
	$h = $a[3];
	$s = $a[4];
	$q = $a[5];
	$sh = $a[6];
	$x = trim($a[7]);
	$y = trim($a[8]);
	if (isset($a[9])) {
		$force = trim($a[9]);
	} else {
		$force = false;
	}
	
	define('PATH', ROOT . DS . 'albums' . DS . $path);
	$ext = returnExt($file);
	
	if ($s != 2) {
		$fn .= "_{$w}_{$h}_{$s}_{$q}_{$sh}_{$x}_{$y}." . $ext;
	}
	
	$path_to_cache = PATH . DS . 'cache' . DS . $fn;
	
	$noob = false;
	if (!file_exists($path_to_cache)) {
		$noob = true;
		$original = PATH . DS . 'lg' . DS . $file;
		if ($s == 2) {
			copy($original, $path_to_cache);
		} else {
			if (!defined('MAGICK_PATH')) {
				define('MAGICK_PATH_FINAL', 'convert');
			} else if (strpos(strtolower(MAGICK_PATH), 'c:\\') !== false) {
				define('MAGICK_PATH_FINAL', '"' . MAGICK_PATH . '"');	
			} else {
				define('MAGICK_PATH_FINAL', MAGICK_PATH);	
			}
			if (!defined('FORCE_GD')) {
				define('FORCE_GD', 0);
			}
			require(ROOT . DS . 'app' . DS . 'vendors' . DS . 'bradleyboy' . DS . 'darkroom.php');
			$d = new Darkroom;
			$d->develop($original, $path_to_cache, $w, $h, $q, $s, null, $sh, $x, $y, $force); 
		}
	}

	$mtime = filemtime($path_to_cache);
	$etag = md5($path_to_cache . $mtime);
	
	if (!$noob) {
		if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && ($_SERVER['HTTP_IF_NONE_MATCH'] == $etag)) {
			header("HTTP/1.1 304 Not Modified");
		    exit;
		}
	
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= filemtime($path_to_cache))) {
			header("HTTP/1.1 304 Not Modified");
		    exit;
		}	
	}
	
	$specs = getimagesize($path_to_cache);
	header('Content-type: ' . $specs['mime']);
	header('Content-length: ' . filesize($path_to_cache));
	header('Cache-Control: public');
	header('Expires: ' . gmdate('D, d M Y H:i:s', strtotime('+1 year')));
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($path_to_cache)));
	header('ETag: ' . $etag);
	die(file_get_contents($path_to_cache));
?>