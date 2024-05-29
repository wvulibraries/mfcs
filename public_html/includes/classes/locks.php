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
		
		$row = mysqli_fetch_array($sqlResult['result']);

		if ((int)$row["COUNT(*)"] != 0) {
			localvars::add("lockUsername",$row['username']);
			localvars::add("lockDate",date("D,  M d Y H:i",$row['date']));
			return $row['ID'];
		}
		return FALSE;

	}

	public static function check_for_update($objectID,$type) {


		$lockID = self::is_locked($objectID,$type);

		// Make sure we have a Lock ID from the POST
		if (!isset(mfcs::$engine->cleanPost['MYSQL']['lockID'])) {
			errorHandle::errorMsg("Lock ID is missing from POST. (Please report this to developers)");
			return FALSE;
		}

		// Make sure the object is locked
		if ($lockID === FALSE) {
			errorHandle::errorMsg("Object was not locked for editing.");
			return FALSE;
		}

		// Make sure that the database lock ID matches the ID that was given
		if ($lockID != mfcs::$engine->cleanPost['MYSQL']['lockID']) {
			errorHandle::errorMsg("Lock IDs do not match! (Most likely cause is you have this item for edit in multiple windows or Someone else 'Stole' your session.)");
			return FALSE;
		}

		// We are good to go. Repopulate the local variable with the current Lock ID
		// This will be used for drawing the form after submission.
		localvars::add("lockID",$lockID);

		return TRUE;

	}

	public static function unlock_by_lockID($lockID) {

		$sql       = sprintf("SELECT `type`, `typeID` FROM `locks` WHERE `ID`='%s'",
			mfcs::$engine->openDB->escape($lockID)
			);
		$sqlResult = mfcs::$engine->openDB->query($sql);
		
		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}
		
		$row  = mysqli_fetch_array($sqlResult['result']);

		return self::unlock($row['typeID'],$row['type']);

	}

}
?>