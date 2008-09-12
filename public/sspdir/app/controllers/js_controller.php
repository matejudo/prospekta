<?php

class JsController extends AppController {
	// Models needed for this controller
	var $uses = array();
	
	function translate($lang) {
		Configure::write('Config.language', $lang);
		$this->render('translate', 'ajax');
	}
}

?>