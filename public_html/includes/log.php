<?php

class log {

	public static function insert($action,$objectID=0,$formID=0) {

		$sql       = sprintf("INSERT INTO `logs` (`username`,`IP`,`action`,`objectID`,`formID`) VALUES('%s','%s','%s','%s','%s')",
			mfcs::$engine->openDB->escape(sessionGet('username')),
			mfcs::$engine->openDB->escape($_SERVER['REMOTE_ADDR']),
			mfcs::$engine->openDB->escape($action),
			mfcs::$engine->openDB->escape($objectID),
			mfcs::$engine->openDB->escape($formID)
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