<?php

/* 	
	You can use this file to set specific variables outside the SlideShowPro Director core,
	so updates won't affect them. Warning: This is for advanced users only, those with a 
	proper understanding of PHP and its' syntax.
*/

/*
	SESSION SAVE PATH
	A common use of this file is to set a custom session save path, as required by your host.
	Uncomment out the line below and replace the path with your host's session
	save path.
*/

	// session_save_path('/path/from/your/host');
	
/*
	MAGICK PATH
	If the ImageMagick image processing library is installed on your server but is not in the
	server's path, you can enter the direct path to the convert function here. If you aren't sure
	what path to use, contact your hosting provider.
*/

	// define('MAGICK_PATH', '/path/to/convert');
	
/*
	FORCE GD
	If you are having issues with ImageMagick or with a blank screen when trying to access
	Director, set the following to true.
*/

	// define('FORCE_GD', false);
	
/*
	SALT
	This random string helps secure your Director installation. Enter a random, alphanumeric string
	or passphrase below. It can be anything you want, just make sure it is not something that 
	someone would guess. IMPORTANT: For best performance, this string should be longer than 8 characters.
*/

	// define('SALT', 'mysaltedstring');
	
?>