<?php

class duplicates {

	public static function updateDupeTable($formID,$objectID,$data) {

		// trans begin

		// wipe the old dupe information
		$sql       = sprintf("DELETE FROM `dupeMatching` WHERE `formID`='%s' AND `objectID`='%s'",
			$engine->openDB->escape($formID),
			$engine->openDB->escape($objectID)
			);
		$sqlResult = $engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			$engine->openDB->transRollback();
			$engine->openDB->transEnd();
			errorHandle::newError(__METHOD__."() - removing from duplicate table: ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		//insert data
		foreach ($data as $name=>$raw) {
			$sql       = sprintf("INSERT INTO `dupeMatching` (`formID`,`objectID`,`field`,`value`) VALUES('%s','%s','%s','%s')",
				$engine->openDB->escape($formID),
				$engine->openDB->escape($objectID),
				$engine->openDB->escape($name),
				$engine->cleanPost['MYSQL'][$name]
			);
			$sqlResult = $engine->openDB->query($sql);

			if (!$sqlResult['result']) {
				$engine->openDB->transRollback();
				$engine->openDB->transEnd();

				errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
				return FALSE;
			}
		}

		// trans commit
		$engine->openDB->transCommit();
		$engine->openDB->transEnd();

		return TRUE;
	}

	public static function isDupe($formID,$field,$value,$objectID=NULL) {
		$sql = sprintf("SELECT COUNT(*) FROM `dupeMatching` WHERE `formID`='%s' AND `field`='%s' AND `value`='%s' %s",
			mfcs::$engine->openDB->escape($formID),
			mfcs::$engine->openDB->escape($field),
			mfcs::$engine->openDB->escape($value),
			(!isnull($objectID))?"AND `objectID`!='".mfcs::$engine->openDB->escape($objectID)."'":""
		);

		$sqlResult = mfcs::$engine->openDB->query($sql);

		if ($sqlResult['result'] === FALSE) {
			return TRUE;
		}

		$row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);

		// we return TRUE on Error, because if a dupe is encountered we want it to fail out.

		if ((INT)$row['COUNT(*)'] > 0) {
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
	
}

?>