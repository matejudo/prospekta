<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<title><?php echo(strip_tags($_GET['title'])); ?></title> 
		<style type="text/css" media="screen">
		/* <![CDATA[ */
			* { margin:0; padding:0; }
		/* ]]> */
		</style>
		
	</head>
	
	<body>
    	<img src="<?php echo(strip_tags($_GET['src'])); ?>" width="<?php echo(strip_tags($_GET['w'])); ?>" height="<?php echo(strip_tags($_GET['h'])); ?>" alt="<?php echo(strip_tags($_GET['title'])); ?>">
	</body>
</html>