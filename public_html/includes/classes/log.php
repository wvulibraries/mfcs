<?php

class log {

	// copilot refactor 2024-04-24
	public static function insert($action, $objectID = 0, $formID = 0, $info = null)
	{
		$username = mfcs::$engine->openDB->escape(users::user('username'));
		$ip = mfcs::$engine->openDB->escape($_SERVER['REMOTE_ADDR']);
		$action = mfcs::$engine->openDB->escape($action);
		$objectID = ($objectID !== null) ? mfcs::$engine->openDB->escape($objectID) : 'NULL';
		$formID = mfcs::$engine->openDB->escape($formID);
		$info = mfcs::$engine->openDB->escape($info);
		$date = time();

		$sql = "INSERT INTO `logs` (`username`, `IP`, `action`, `objectID`, `formID`, `info`, `date`) 
				VALUES ('$username', '$ip', '$action', $objectID, '$formID', '$info', '$date')";
		$sqlResult = mfcs::$engine->openDB->query($sql);

		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__ . "() - : " . $sqlResult['error'], errorHandle::DEBUG);
			return false;
		}

		return true;
	}

	// $actions = array of actions
	public static function pull_actions($actions, $objectID)
	{
		if (!is_array($actions)) {
			return array();
		}

		$blame = array();

		foreach ($actions as $action) {
			$sql = "SELECT `username`, `date` FROM `logs` WHERE `objectID` = ? AND `action` = ?";
			$params = [mfcs::$engine->openDB->escape($objectID), mfcs::$engine->openDB->escape($action)];
			$sqlResult = mfcs::$engine->openDB->query($sql, $params);

			if (!$sqlResult['result']) {
				errorHandle::newError(__METHOD__ . "() - : " . $sqlResult['error'], errorHandle::DEBUG);
				return array();
			}

			while ($row = mysqli_fetch_array($sqlResult['result'], MYSQLI_ASSOC)) {
				$blame[] = [$row['username'], date('D, d M Y H:i', $row['date'])];
			}
		}

		return $blame;
	}

}

?>