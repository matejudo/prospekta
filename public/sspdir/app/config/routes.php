<?php
/* SVN FILE: $Id: routes.php 2951 2006-05-25 22:12:33Z phpnut $ */
/**
 * Short description for file.
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different urls to chosen controllers and their actions (functions).
 *
 * PHP versions 4 and 5
 *
 * CakePHP :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright (c)	2006, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright (c) 2006, Cake Software Foundation, Inc.
 * @link				http://www.cakefoundation.org/projects/info/cakephp CakePHP Project
 * @package			cake
 * @subpackage		cake.app.config
 * @since			CakePHP v 0.2.9
 * @version			$Revision: 2951 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2006-05-25 17:12:33 -0500 (Thu, 25 May 2006) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Here, we are connecting '/' (base path) to controller called 'Pages',
 * its action called 'display', and we pass a param to select the view file
 * to use (in this case, /app/views/pages/home.thtml)...
 */
	$Route->connect('/snapshot', array('controller' => 'site', 'action' => 'index'));
	$Route->connect('/dashboard', array('controller' => 'site', 'action' => 'old_index'));
	$Route->connect('/', array('controller' => 'site', 'action' => 'index'));
?>