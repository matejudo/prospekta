<?php
class DirectorComponent extends Object {
   	var $controller = true;
	var $uses = array('Account', 'Image');
	var $components = array('Kodak');
 
    function startup (&$controller) {
        $this->controller = &$controller;
    }

	////
	// Fetch account
	////
	function fetchAccount() {
		$cache_path = DIR_CACHE . DS . 'account';
		$account = unserialize(cache($cache_path, null, '+1 day'));
		if (empty($account)) {
			loadModel('Account');
			$this->Account =& new Account();
			$account = $this->Account->find();
			cache($cache_path, serialize($account));
		}
		return $account;
	}
	
	////
	// Check email for a user
	////
	function checkMail($id) {
		loadModel('User');
		$this->User =& new User(); 
		$user = $this->User->find($id);
		return $user['User']['email'];
	}
	
	////
	// Ensure cache folder is in place TODO: Remove and move folder creation to reg. process
	////
	function ensureCache($path) {
		if (!is_dir(dirname(CACHE . $path))) {
			uses('Folder');
			new Folder(dirname(CACHE . $path), true);
		}
	}
	
	////
	// Get all slideshows
	////
	function fetchShows() {
		$cache_path = DIR_CACHE . DS . 'shows';
		$shows = cache($cache_path, null, '+1 year');
		if ($shows == 'noshow') {
			$shows = array();
		} elseif (empty($shows)) {
			loadModel('Slideshow');
			$this->Slideshow =& new Slideshow();
			$shows = $this->Slideshow->findAll();
			if (empty($shows)) { 
				cache($cache_path, 'noshow');
			} else {
				cache($cache_path, serialize($shows));
			}
		} else {
			$shows = unserialize($shows);
		}
		return $shows;
	}
	
	////
	// Generate random string
	////
	function randomStr($len = 6) {
		return substr(md5(uniqid(microtime())), 0, $len);
	}
	
	////
	// Central directory creation logic
	// Creates a directory if it does not exits
 	////
	function makeDir($dir, $perms = '0777') {
		if (!is_dir($dir)) {
			umask(0);
			if (@mkdir($dir, octdec($perms))) {
				return true;
			} else {
				return false;
			}	
		} else {
			return true;
		}
	}
	
	////
	// Check for import folders
	////
	function checkImports() {
		if (is_dir(IMPORTS) && $handle = opendir(IMPORTS)) {
		    $folders = array();

		    while (false !== ($file = readdir($handle))) {
				$full_path = IMPORTS . DS . $file;
		        if (is_dir($full_path) && file_exists($full_path . DS . 'images.xml') && $file != '.' && $file != '..') {
					$folders[] = $file;
				}
		    }
		
		    closedir($handle);
			return $folders;
		} else {
			return array();
		}
	}
	
	
	////
	// Set permissions on a directory
	////
	function setPerms($dir, $perms = '0777') {
		if (!is_dir($dir)) {
			return $this->makeDir($dir);
		} elseif (is_writable($dir)) {
			return true;
		} else {
			$current_perms = substr(sprintf('%o', fileperms($dir)), -4);
			settype($current_perms, "string"); 
			if ($current_perms === $perms) {
				return true;
			} else {
				$mask = umask(0);                 
				if (@chmod($path, octdec($perms))) {
					umask($mask);
					return true;
				} else {
					umask($mask);
					return false;
				}
		    }
		}
	}
	
	////
	// Make sure album-audio and album-thumb have the correct perms
	////
	function setOtherPerms() {
		if ($this->setPerms(AUDIO)) {
			return true;
		} else {
			return false;
		}
	}
	
	////
	// Create album subdirectories
	////
	function setAlbumPerms($path) {
		if (empty($path)) {
			return false;
		} else {
			$path = ALBUMS . DS . $path;
			$lg = $path . DS . 'lg';
			$cache = $path . DS . 'cache';
		
			if ($this->setPerms($lg) && $this->setPerms($cache)) {
				return true;
			} else {
				return false;
			}
		}
	}
	
	////
	// Process permissions for album subdirectories
	////
	function createAlbumDirs($path) {
		$path = ALBUMS . DS . $path;
		$lg = $path . DS . 'lg';
		$director = $path . DS . 'director';
		$cache = $path . DS . 'cache';
		
		if ($this->makeDir($lg) && $this->makeDir($director) && $this->makeDir($cache)) {
			return true;
		} else {
			return false;
		}
	}
	
	////
	// Search a directory for a filename using a regular expression
	// Found in PHP docs: http://us3.php.net/manual/en/function.file-exists.php#64908
	////
	
	function regExpSearch($regExp, $dir, $regType='P', $case='') {
		$func = ( $regType == 'P' ) ? 'preg_match' : 'ereg' . $case;
		$open = opendir($dir);
		$files = array();
		while( ($file = readdir($open)) !== false ) {
			if ($func($regExp, $file) ) {
				$files[] = $file;
			}
		}
		return $files;
	}
	
	////
	// Grab the extension of of any file
	////
	function returnExt($file) {
		$pos = strrpos($file, '.');
		return strtolower(substr($file, $pos+1, strlen($file)));
	}

	////
	// Grab all files in a directory
	////
	function directory($dir, $filters = 'all') {
		if ($filters == 'accepted') { $filters = 'jpg,JPG,JPEG,jpeg,gif,GIF,png,PNG,swf,SWF,flv,FLV,mov,MOV,mp4,MP4,m4v,MV4,m4a,M4A,3gp,3GP,3g2,3G2'; }
		$handle = opendir($dir);
		$files = array();
		if ($filters == "all"):
			while (($file = readdir($handle))!==false):
				$files[] = $file;
			endwhile;
		endif;
		if ($filters != "all"):
			$filters = explode(",", $filters);
			while (($file = readdir($handle))!==false):
				for ($f=0; $f< sizeof($filters); $f++):
					$system = explode(".", $file);
					$count = count($system);
					if ($system[$count-1] == $filters[$f]):
						$files[] = $file;
					endif;
				endfor;
			endwhile;
		endif;
		closedir($handle);
		return $files;
	}
	
	////
	// Return string describing large thumbnail specs
	////
	function generateDesc($specs, $thumbs = false) {
		if ($thumbs) {
			$out = 'Thumbnails processed at ';
		} else {
			$out = 'Large images processed at ';
		}
		$out .= $specs['quality'] . ' quality with a sharpening factor of ' . $specs['sharpening'] . '.';
		return $out;
	}
	
	////
	// Process new image content templates
	////
	function postProcessTemplates($album, $image_id) {
		loadModel('Image');
		$this->Image =& new Image(); 
		
		$title = $album['Album']['title_template'];
		$link = $album['Album']['link_template'];
		$caption = $album['Album']['caption_template'];
		$this->Image->id = $image_id;
		
		if (!empty($title)) {
			$this->Image->formTitle($title);
		}
		
		if (!empty($caption)) {
			$this->Image->formCaption($caption);
		}
		
		if (!empty($link)) {
			$this->Image->formLink(urldecode($link), $album);
		}
	}
	
	////
	// Recursive Directory Removal
	////
	function rmdirr($dir) {
	   	if (!$dh = @opendir($dir)) return;
	   	while (($obj = readdir($dh))) {
	       	if ($obj=='.' || $obj=='..') continue;
	       	$path = $dir.'/'.$obj;
			if (is_dir($path)) {
				@$this->rmdirr($path);
			} else {
				@unlink($path);
			}
	   	}
	 	closedir($dh);
	   	rmdir($dir);
	}

	////
	// Transform a string (e.g. 15MB) into an actual byte representation
	////
	function returnBytes($val) {
	   $val = trim($val);
	   $last = strtolower($val{strlen($val)-1});
	   switch($last) {
	       case 'g':
	           	$val *= 1024;
	       case 'm':
	           	$val *= 1024;
	       case 'k':
	           	$val *= 1024;
	   }
	   return $val;
	}

	////
	// Ye old autop function via PhotoMatt.net
	// This function is GPL'd (I believe) and is not covered by the Director license
	////
	function autop($pee, $br=1) {
		$pee = preg_replace("/(\r\n|\n|\r)/", "\n", $pee); // cross-platform newlines
		$pee = preg_replace("/\n\n+/", "\n\n", $pee); // take care of duplicates
		$pee = preg_replace('/\n?(.+?)(\n\n|\z)/s', "<p>$1</p>\n", $pee); // make paragraphs, including one at the end
		if ($br) $pee = preg_replace('|(?<!</p>)\s*\n|', "<br />\n", $pee); // optionally make line breaks
		return $pee;
	}
}

?>