<?php

class locks {

	public static function lock($objectID, $type) {

		if (self::is_locked($objectID,$type)) {
			errorHandle::errorMsg("Object already locked.");
			return FALSE;
		}

		$sql       = sprintf("INSERT INTO `locks` (`type`,`typeID`,`user`,`date`) VALUES('%s','%s','%s','%s')",
			mfcs::$engine->openDB->escape($type),
			mfcs::$engine->openDB->escape($objectID),
			mfcs::$engine->openDB->escape(users::user('ID')),
			time()
			);
		$sqlResult = mfcs::$engine->openDB->query($sql);
		
		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}
		
		localvars::add("lockID",$sqlResult['id']);

		return $sqlResult['id'];

	}

	public static function unlock($objectID, $type) {

		$sql       = sprintf("DELETE FROM `locks` WHERE `type`='%s' AND `typeID`='%s'",
			mfcs::$engine->openDB->escape($type),
			mfcs::$engine->openDB->escape($objectID)
			);
		$sqlResult = mfcs::$engine->openDB->query($sql);
		
		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - unlocking object: ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}
		
		return TRUE;

	}

	/**
	 * Determines if the given object is locked for editing
	 * @param  int  $objectID the MySQL database ID number for the object
	 * @param  string  $type     the type of "thing" (object or form) that the ID number corresponds to. 
	 * @return boolean|int           If not locked, boolean false is returned. If locked, lock ID is returned
	 */
	public static function is_locked($objectID, $type) {

		$sql       = sprintf("SELECT COUNT(*), `locks`.ID as ID, `users`.`username` as `username`,`date` from `locks` LEFT JOIN users on `locks`.`user`=`users`.`ID` WHERE `type`='%s' AND `typeID`='%s'",
			mfcs::$engine->openDB->escape($type),
			mfcs::$engine->openDB->escape($objectID)
			);
		$sqlResult = mfcs::$engine->openDB->query($sql);
		
		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}
		
		$row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);

		if ((int)$row["COUNT(*)"] != 0) {
			localvars::add("lockUsername",$row['username']);
			localvars::add("lockDate",date("D,  M d Y H:i",$row['date']));
			return $row['ID'];
		}
		return FALSE;

	}

}
?>