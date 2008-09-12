<?php
	
	include_once('clock_in.php');
	
	// We recommend adding this file as a cron job to run once a day
	
	// Clear any album caches that have not been used in 1 week
	
	$files = glob($albums . $ds . '*' . $ds . '*' . $ds . 'cache' . $ds . '*');
	
	foreach ($files as $file) {
		if (fileatime($file) < strtotime('-1 week')) {
			@unlink($file);
		}
	}

?>