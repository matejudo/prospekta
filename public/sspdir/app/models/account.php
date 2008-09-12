<?php

class Account extends AppModel {
    var $name = 'Account';
	var $useTable = 'account';
					
	function afterSave() {
		cache(DIR_CACHE . DS . 'account', null, '-1 day');
		return true;
	}
}

?>