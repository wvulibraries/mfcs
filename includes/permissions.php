<?php

class mfcsPerms {

	private static function getCount($formID,$username,$type) {

		$sql       = sprintf("SELECT COUNT(permissions.ID) FROM permissions LEFT JOIN users on users.ID=permissions.userID WHERE permissions.formID='%s' AND users.username='%s'",
            mfcs::$engine->openDB->escape($formID),
            mfcs::$engine->openDB->escape($username)
    		);
    	$sqlResult = mfcs::$engine->openDB->query($sql);

    	if (!$sqlResult['result']) {
    		errorHandle::newError(__METHOD__."() - ".$sqlResult['error'], errorHandle::DEBUG);
    		return FALSE;
    	}

    	$row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);

    	if ((int)$row['COUNT(permissions.ID)'] > 0) {
    		return TRUE;
    	}
    	else {
    		return FALSE;
    	}

    	return FALSE;

	}

	public static function isAdmin($formID, $username = NULL) {
		if (isnull($username)) $username = sessionGet("username");

		return self::getCount($formID,$username,mfcs::AUTH_ADMIN);
	}

	public static function isEditor($formID, $username = NULL) {
		if (isnull($username)) $username = sessionGet("username");

		if (self::getCount($formID,$username,mfcs::AUTH_ADMIN) === TRUE) return TRUE;

		return self::getCount($formID,$username,mfcs::AUTH_ENTRY);
	}

	public static function isViewer($formID, $username = NULL) {

		if (isnull($username)) $username = sessionGet("username");

		if (self::getCount($formID,$username,mfcs::AUTH_ADMIN) === TRUE) return TRUE;
		if (self::getCount($formID,$username,mfcs::AUTH_ENTRY) === TRUE) return TRUE;
		
		return self::getCount($formID,$username,mfcs::AUTH_VIEW);

	}

	public static function add($userID,$formID,$type) {

		$sql       = sprintf("INSERT INTO `permissions` (userID,formID,type) VALUES('%s','%s','%s')",
			mfcs::$engine->openDB->escape($userID),
			mfcs::$engine->openDB->escape($formID),
			mfcs::$engine->openDB->escape($type)
			);
		$sqlResult = mfcs::$engine->openDB->query($sql);
		
		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}
		
		return TRUE;

	}

	public static function delete($id = NULL) {

		if (!isnull($id)) {
			$whereClause = sprintf("WHERE `formID`='%s'",
				mfcs::$engine->openDB->escape($id)
				);
		}
		else {
			$whereClause = "";
		}

		$sql       = sprintf("DELETE FROM `permissions` %s",
			$whereClause
			);
		$sqlResult = mfcs::$engine->openDB->query($sql);
		
		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}
		
		return TRUE;

	}

}

?>