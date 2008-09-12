<?php 
	////
	// This is a passthrough file. Requests
	// are handled by the cron.php file
	// in app/webroot/
	//
	// If you have the ability to run cron jobs on your server,
	// we suggest adding this file to your daily cron tasks. It
	// deletes any cached album file that has not been accessed
	// in the last week. You can also run this task manually
	// from the account preferences screen.
	////

	$ds = DIRECTORY_SEPARATOR;
	require dirname(__FILE__) . $ds . 'app' . $ds . 'webroot' . $ds . 'cron.php';
	
?>