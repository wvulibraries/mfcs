<?php

class mfcsPerms {

	public static function isadmin($formID, $username = NULL) {

	}

	public static function isEditor($formID, $username = NULL) {

	}

	public static function isViewer($formID, $username = NULL) {

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