<?php

class mfcsPerms {

	private static function getCount($formID,$username,$type) {
		$sql = sprintf("SELECT COUNT(permissions.ID) FROM permissions LEFT JOIN users on users.ID=permissions.userID WHERE permissions.formID='%s' AND users.username='%s'",
            mfcs::$engine->openDB->escape($formID),
            mfcs::$engine->openDB->escape($username));
    	$sqlResult = mfcs::$engine->openDB->query($sql);

    	if (!$sqlResult['result']) {
    		errorHandle::newError(__METHOD__."() - ".$sqlResult['error'], errorHandle::DEBUG);
    		return FALSE;
    	}

    	$row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);
    	if ((int)$row['COUNT(permissions.ID)'] > 0) return TRUE;
		return FALSE;
	}

	public static function evaluatePageAccess($pageAccessBit, $username = NULL){
		if(isnull($username)) $username = sessionGet("username");

		$userBit = self::evaluateUserBits($username);
		return ($pageAccessBit <= $userBit ? TRUE : FALSE);
	}

	public static function hasAdminAccess($username = NULL){
		if(isnull($username)) $username = sessionGet("username");
		$user = users::get($username);
		return (strtolower($user['status']) == 'admin' ? TRUE : FALSE);
	}

	public static function hasEditorAccess($username = NULL){
		if(isnull($username)) $username = sessionGet("username");
		$user = users::get($username);
		return (strtolower($user['status']) == 'editor' ? TRUE : FALSE);
	}

	public static function hasUserAccess($username = NULL){
		if(isnull($username)) $username = sessionGet("username");
		$user = users::get($username);
		return (strtolower($user['status']) == 'user' ? TRUE : FALSE);
	}

	public static function isAdmin($formID, $username = NULL) {
		if (isnull($username)) $username = sessionGet("username");
		return self::getCount($formID,$username,mfcs::AUTH_ADMIN) || trim(strtolower(users::user('status','user'))) == 'admin';
	}

	public static function isEditor($formID, $username = NULL) {
		if (isnull($username)) $username = sessionGet("username");
		return self::getCount($formID,$username,mfcs::AUTH_ENTRY) || self::isAdmin($formID,$username);
	}

	public static function isViewer($formID, $username = NULL) {
		if (isnull($username)) $username = sessionGet("username");
		return self::getCount($formID,$username,mfcs::AUTH_VIEW) || self::isEditor($formID,$username);
	}

	public static function isActive($username = NULL){
		if(isnull($username)) $username = sessionGet('username');
		$user = users::get($username);
		return ($user['active'] == 1 ? TRUE : FALSE);
	}

	public static function add($userID,$formID,$type) {
		$sql = sprintf("INSERT INTO `permissions` (userID,formID,type) VALUES('%s','%s','%s')",
			mfcs::$engine->openDB->escape($userID),
			mfcs::$engine->openDB->escape($formID),
			mfcs::$engine->openDB->escape($type));
		$sqlResult = mfcs::$engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		return TRUE;
	}

	public static function delete($id) {
		$sql = sprintf("DELETE FROM `permissions` WHERE `formID`='%s'",
			mfcs::$engine->openDB->escape($id));
		$sqlResult = mfcs::$engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		return TRUE;
	}


	private static function evaluateUserBits($username){
		if(self::hasAdminAccess($username) && self::isActive($username)){
			return 3;
		}
		elseif(self::hasEditorAccess($username) && self::isActive($username)){
			return 2;
		}
		elseif(self::hasUserAccess($username) && self::isActive($username)) {
			return 1;
		}
		else {
			return 0;
		}
	}

	// takes valid formID
	// returns array of users. permission => array of usernames
	public static function permissions_for_form($formID) {

		$sql       = sprintf("select `users`.`username` as `username`, `permissions`.`type` as `type` FROM `permissions` LEFT JOIN `users` on `users`.`ID`=`permissions`.`userID` WHERE `formID`='%s'",
			mfcs::$engine->openDB->escape($formID)
			);
		$sqlResult = mfcs::$engine->openDB->query($sql);
		
		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}
		
		$permissions = array();

		while($row       = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {

			if (!isset($permissions[$row['type']])) $permissions[$row['type']] = array();

			$permissions[$row['type']][] = $row['username'];

		}

		return $permissions;

	}

	// converts permission type integer to human readable
	public static function type_is($type) {

		switch ($type) {
			case mfcs::AUTH_VIEW:
				return "View";
				break;
			case mfcs::AUTH_ENTRY:
				return "Edit";
				break;
			case mfcs::AUTH_ADMIN:
				return "Form Admin";
				break;
			default:
				return "Error";
				break;
		}

		return "Error!";

	}

}

?>