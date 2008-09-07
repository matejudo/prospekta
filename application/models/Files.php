<?php

class Files
{

	public function listFiles($folder = "/")
	{
		$files = array();
		$dir = "uploads/" . $folder;
	    if ($dh = opendir($dir)) 
	    {
	        while (($file = readdir($dh)) !== false) {
	        	$curfile = new stdClass();
	        	$curfile->name = $file;
	        	$curfile->type = filetype($dir ."/". $file);
	        	$curfile->size = round(filesize($dir ."/". $file)/1024, 2) . " KB";
	        	$curfile->modified = date("d.m.Y. G:i:s", filemtime($dir ."/". $file));
	        	$curfile->perms = $this->readablePerms(fileperms($dir ."/". $file));
	        	$curfile->owner	= fileowner($dir ."/". $file);
	        	$curfile->extension = $this->findexts ($dir ."/". $file);
				array_push($files, $curfile);
	        }
        	closedir($dh);
    	}
    	return $files;
	}
	
	function readablePerms($perms)
	{
		if (($perms & 0xC000) == 0xC000) {
		    // Socket
		    $info = 's';
		} elseif (($perms & 0xA000) == 0xA000) {
		    // Symbolic Link
		    $info = 'l';
		} elseif (($perms & 0x8000) == 0x8000) {
		    // Regular
		    $info = '-';
		} elseif (($perms & 0x6000) == 0x6000) {
		    // Block special
		    $info = 'b';
		} elseif (($perms & 0x4000) == 0x4000) {
		    // Directory
		    $info = 'd';
		} elseif (($perms & 0x2000) == 0x2000) {
		    // Character special
		    $info = 'c';
		} elseif (($perms & 0x1000) == 0x1000) {
		    // FIFO pipe
		    $info = 'p';
		} else {
		    // Unknown
		    $info = 'u';
		}
		
		// Owner
		$info .= (($perms & 0x0100) ? 'r' : '-');
		$info .= (($perms & 0x0080) ? 'w' : '-');
		$info .= (($perms & 0x0040) ?
		            (($perms & 0x0800) ? 's' : 'x' ) :
		            (($perms & 0x0800) ? 'S' : '-'));
		
		// Group
		$info .= (($perms & 0x0020) ? 'r' : '-');
		$info .= (($perms & 0x0010) ? 'w' : '-');
		$info .= (($perms & 0x0008) ?
		            (($perms & 0x0400) ? 's' : 'x' ) :
		            (($perms & 0x0400) ? 'S' : '-'));
		
		// World
		$info .= (($perms & 0x0004) ? 'r' : '-');
		$info .= (($perms & 0x0002) ? 'w' : '-');
		$info .= (($perms & 0x0001) ?
		            (($perms & 0x0200) ? 't' : 'x' ) :
		            (($perms & 0x0200) ? 'T' : '-'));
		
		return $info;
	}
	
	function findexts ($filename) 
	{ 
		$filename = strtolower($filename) ; 
		$exts = split("[/\\.]", $filename) ; 
		$n = count($exts)-1; 
		$exts = $exts[$n]; 
		return $exts; 
	}
	
	public function unzip($files, $curdir)
	{
		foreach($files as $key => $value)
		{
			$path = pathinfo($_SERVER["PHP_SELF"]);
			$file = realpath($_SERVER["DOCUMENT_ROOT"] . "/" . $path["dirname"] . "/uploads/" . $value);
		    $zip = new ZipArchive;
     		$res = $zip->open($file);
			if ($res === TRUE) {
				$zip->extractTo($_SERVER["DOCUMENT_ROOT"] . "/" . $path["dirname"] . "/uploads/" . $curdir);
				$zip->close();
				echo 'ok';
			} 
			else 
			{
				echo 'failed';
			}
		}
	}
}