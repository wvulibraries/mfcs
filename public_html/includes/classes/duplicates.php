<?php

class duplicates {

	public static function updateDupeTable($formID,$objectID,$data) {

		// trans begin
		$result = mfcs::$engine->openDB->transBegin("objects");
		if ($result !== TRUE) {
			errorHandle::newError(__METHOD__."() - unable to start database transactions", errorHandle::DEBUG);
			return FALSE;
		}

		// wipe the old dupe information
		if (!self::delete($objectID,$formID)) {
			mfcs::$engine->openDB->transRollback();
			mfcs::$engine->openDB->transEnd();
			errorHandle::newError(__METHOD__."() - removing from duplicate table: ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		//insert data
		foreach ($data as $name=>$raw) {

			if (is_array($raw)) continue;

			if (!isset(mfcs::$engine->cleanPost['MYSQL'][$name]) || isempty(mfcs::$engine->cleanPost['MYSQL'][$name])) {
				if (!isempty($raw)) {
					http::setPost($name,$raw);
					$postSet = TRUE;
				}
				else {
					continue;
				}
			}

			$sql       = sprintf("INSERT INTO `dupeMatching` (`formID`,`objectID`,`field`,`value`) VALUES('%s','%s','%s','%s')",
				mfcs::$engine->openDB->escape($formID),
				mfcs::$engine->openDB->escape($objectID),
				mfcs::$engine->openDB->escape($name),
				mfcs::$engine->cleanPost['MYSQL'][$name] //@TODO this should use data
			);
			$sqlResult = mfcs::$engine->openDB->query($sql);

			if (isset($postSet) && $postSet === TRUE) {
				http::setPost($name,"");
			}

			if (!$sqlResult['result']) {
				mfcs::$engine->openDB->transRollback();
				mfcs::$engine->openDB->transEnd();

				errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
				return FALSE;
			}
		}

		// trans commit
		mfcs::$engine->openDB->transCommit();
		mfcs::$engine->openDB->transEnd();

		return TRUE;
	}

	public static function isDupe($formID,$field,$value,$objectID=NULL) {

		if ($field == "idno") {
			$sql = sprintf("SELECT COUNT(*) FROM `dupeMatching` WHERE `field`='idno' AND `value`='%s' %s",
				mfcs::$engine->openDB->escape($value),
				(!isnull($objectID))?"AND `objectID`!='".mfcs::$engine->openDB->escape($objectID)."'":""
				);
		}
		else {
			$sql = sprintf("SELECT COUNT(*) FROM `dupeMatching` WHERE `formID`='%s' AND `field`='%s' AND `value`='%s' %s",
				mfcs::$engine->openDB->escape($formID),
				mfcs::$engine->openDB->escape($field),
				mfcs::$engine->openDB->escape($value),
				(!isnull($objectID))?"AND `objectID`!='".mfcs::$engine->openDB->escape($objectID)."'":""
				);
		}

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

	public static function delete($objectID,$formID=NULL) {

		$sql       = sprintf("DELETE FROM `dupeMatching` WHERE `objectID`='%s'%s",
			mfcs::$engine->openDB->escape($objectID),
			isnull($formID)?"":sprintf("AND `formID`='%s'",mfcs::$engine->openDB->escape($formID))
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
