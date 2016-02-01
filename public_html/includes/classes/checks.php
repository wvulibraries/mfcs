<?php

class checks {

	public static function set_ok($name) {

		$sql = sprintf("UPDATE `checks` SET `value`='1' WHERE `name`='%s' LIMIT 1",
			mfcs::$engine->openDB->escape($name)
			);
		$sqlResult = mfcs::$engine->openDB->query($sql);
		
		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		return TRUE;

	}

	public static function set_error($name) {

		$sql = sprintf("UPDATE `checks` SET `value`='0' WHERE `name`='%s' LIMIT 1",
			mfcs::$engine->openDB->escape($name)
			);
		$sqlResult = mfcs::$engine->openDB->query($sql);
		
		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}

		return TRUE;

	}

	// returns TRUE if check value is OK
	// returns FALSE if check value is NOT OK
	// returns NULL if there was a mysql error (should assume NOT OK)
	public static function is_ok($name) {

		$sql       = sprintf("SELECT `value` FROM `checks` WHERE `name`='%s'",
			mfcs::$engine->openDB->escape($name)
			);
		$sqlResult = mfcs::$engine->openDB->query($sql);
		
		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return NULL;
		}
		
		$row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);

		return ($row['value'] === "0")?FALSE:TRUE;

	}

}