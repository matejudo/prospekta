<?php
	
	$ds = DIRECTORY_SEPARATOR;
	$albums = dirname(dirname(dirname(__FILE__))) . $ds . 'albums';
	
	$files = glob($albums . $ds . '*' . $ds . 'cache' . $ds . '*');
	
	foreach ($files as $file) {
		if (fileatime($file) < strtotime('-1 week')) {
			@unlink($file);
		}
	}

?>