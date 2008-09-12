<?php

class User extends AppModel {
    var $name = 'User';
	var $useTable = 'usrs';
	
	function afterFind($result) {
		if (!isset($result[0]['User'])) { return $result; }
		switch($result[0]['User']['perms']) {
			case(1):
				$role = __('You are an Editor', true);
				break;
			case(2):
				$role = __('You are a Contributor', true);
				break;
			default:
				$role = __('You are an Administrator', true);
				break;
		}
		
		$result[0]['User']['role'] = $role;
		return $result;
	}
}

?>