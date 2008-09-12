<?php
	
	/*
		This is a sample link template for SlideShowPro Director.
		To create your own link template, make a copy of this file
		and give it a unique name.
		
		This example shows the template for links opening the original
		image in the same browser window.
	*/
	
	/*
		The displayName variable is the label used to identify
		this template in the dropdown menu.
	*/
	$displayName = 'Open original image in same browser windows';

	/*
		The template variable is the actual link template to create.
		The following variables are available to use:
		
		[full_hr_url] = The full, absolute link to the highest resolution copy
		available for the image. e.g. http://www.myhost.com/ssp_director/albums/album-1/hr/myimage.jpg
		
		[img_src] = the filename of the image (e.g. myimage.jpg)
		
		[img_w] = the width of the image in pixels (e.g. 400)
		
		[img_h] = the height of the image in pixels (e.g. 300)
	*/
	$template = '[full_hr_url]';
	
	/*
		The target variable defines whether the link will open in a new window (0) or the same browser window as the slideshow (1).
		Templates that utilize javascript are not affected by this parameter (you can just set it to 0).
	*/
	$target = 1;

?>