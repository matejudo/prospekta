<?php

	$full = DIR_HOST . '/popup.php?src=[full_hr_url]&w=[img_w]&h=[img_h]&title=[img_src]';
	
	$displayName = __('Open original image in chromeless popup window', true);
	$template = "javascript:if (window.NewWindow) { NewWindow.close(); }; NewWindow=window.open('$full','myWindow','width=[img_w],height=[img_h],toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,titlebar=no');NewWindow.focus(); void(0);";
	$target = 0;

?>