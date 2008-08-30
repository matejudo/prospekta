<?php

class Files
{
	public function listFiles()
	{
		$files = "";
		$dir = "images";
	    if ($dh = opendir($dir)) 
	    {
	        while (($file = readdir($dh)) !== false) {
	            $files .= "filename: $file : filetype: " . filetype($dir ."/". $file) . "<br/>";
	        }
        	closedir($dh);
    	}
    	return $files;
	}
}