<?php

class log {

	public static function insert($action,$objectID=0,$formID=0,$info=NULL) {

		$sql       = sprintf("INSERT INTO `logs` (`username`,`IP`,`action`,`objectID`,`formID`,`info`,`date`) VALUES('%s','%s','%s','%s','%s','%s','%s')",
			mfcs::$engine->openDB->escape(users::user('username')),
			mfcs::$engine->openDB->escape($_SERVER['REMOTE_ADDR']),
			mfcs::$engine->openDB->escape($action),
			mfcs::$engine->openDB->escape($objectID),
			mfcs::$engine->openDB->escape($formID),
			mfcs::$engine->openDB->escape($info),
			time()
			);
		$sqlResult = mfcs::$engine->openDB->query($sql);
		
		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}
		
		return TRUE;

	}

	// $actions = array of actions
	public static function pull_actions($actions,$objectID) {

		if (!is_array($actions)) {
			return array();
		}

		$blame = array();

		foreach ($actions as $action) {

			$sql       = sprintf("SELECT `username`, `date` FROM `logs` WHERE `objectID`='%s' AND `action`='%s'",
				mfcs::$engine->openDB->escape($objectID),
				mfcs::$engine->openDB->escape($action)
				);
			$sqlResult = mfcs::$engine->openDB->query($sql);
			
			if (!$sqlResult['result']) {
				errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
				return array();
			}
			
			while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {

				$blame[] = array($row['username'],date('D, d M Y H:i',$row['date']));

			}

		}

		return $blame;

	}

}

?>