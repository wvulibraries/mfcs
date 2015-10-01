<?php

class log {

	public static function insert($action,$objectID=0,$formID=0,$info=NULL) {

		$sql       = sprintf("INSERT INTO `logs` (`username`,`IP`,`action`,`objectID`,`formID`,`info`) VALUES('%s','%s','%s','%s','%s','%s')",
			mfcs::$engine->openDB->escape(users::user('username')),
			mfcs::$engine->openDB->escape($_SERVER['REMOTE_ADDR']),
			mfcs::$engine->openDB->escape($action),
			mfcs::$engine->openDB->escape($objectID),
			mfcs::$engine->openDB->escape($formID),
			mfcs::$engine->openDB->escape($info)
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