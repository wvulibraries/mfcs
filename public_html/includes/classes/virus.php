<?php

class virus {

	public static function insert_into_table($objectID, $field_name) {

		if (!objects::validID(TRUE,$objectID)) {
			return FALSE;
		}

		// start transactions
		if (mfcs::$engine->openDB->transBegin("objects") !== TRUE) {
			errorHandle::newError(__METHOD__."() - unable to start database transactions", errorHandle::DEBUG);
			return FALSE;
		}

		foreach (self::$insertFieldNames as $fieldname) {
			$sql       = sprintf("INSERT INTO `virusChecks` (`objectID`,`fieldName`,`state`, `timestamp`) VALUES('%s','%s','1','%s')",
				mfcs::$engine->openDB->escape($objID),
				mfcs::$engine->openDB->escape($fieldname),
				time()
				);
			$sqlResult = mfcs::$engine->openDB->query($sql);

			if (!$sqlResult['result']) {

				mfcs::$engine->openDB->transRollback();
				mfcs::$engine->openDB->transEnd();

				errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
				return FALSE;
			}
		}

		// end transactions
		mfcs::$engine->openDB->transCommit();
		mfcs::$engine->openDB->transEnd();

		return TRUE;

	}

	public static function clear_table() {

	}

	public static function scan_file() {

	}

	// 0 = done
	// 1 = needs processing
	// 2 = currently working on
	// 3 = virus found
	public static function set_virus_state($virus_id,$state) {

		$sql       = sprintf("UPDATE `virusChecks` SET `state`='%s' WHERE `ID`='%s'",
			mfcs::$engine->openDB->escape($state),
			mfcs::$engine->openDB->escape($rowID)
			);
		$sqlResult = mfcs::$engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		return TRUE;

	}

	public static function process() {

		$sql       = sprintf("SELECT * FROM `virusChecks` WHERE `state`='1'");
		$sqlResult = $engine->openDB->query($sql);
		
		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}
		
		while ($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {

			// set the state of the row to 2, the "working" state
			self::set_virus_state($row['ID'],"2"); 

			// get the object, and ignore the cache since we are updating in a loop
			$object   = objects::get($row['objectID'],TRUE);
			$files    = $object['data'][$row['fieldName']];
			$assetsID = $files['uuid'];

			foreach file?
				scan_file

		}

	}

}

?>