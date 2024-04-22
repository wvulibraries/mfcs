<?php

class log {
	public static function insert($action, $objectID, $formID, $info = NULL) {
		$db = mfcs::$engine->openDB;
		
		// Validate parameters
		if (empty($action)) {
			errorHandle::newError(__METHOD__."() - Action cannot be empty", errorHandle::DEBUG);
			return FALSE;
		}
	
		// Escape values
		$username = $db->escape(users::user('username'));
		$ip = $db->escape($_SERVER['REMOTE_ADDR']);
		$action = $db->escape($action);
		$objectID = $db->escape($objectID);
		$formID = $db->escape($formID);
		$info = $db->escape($info);
		$date = time();
	
		// Build the SQL query
		$sql = "INSERT INTO `logs` (`username`, `IP`, `action`, `objectID`, `formID`, `info`, `date`) 
				VALUES ('$username', '$ip', '$action', '$objectID', '$formID', '$info', '$date')";
		
		// Execute the query
		$sqlResult = $db->query($sql);
	
		// Check for insertion success
		if ($sqlResult['result']) {
			return TRUE;
		} else {
			errorHandle::newError(__METHOD__."() - Insertion failed: ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}
	}

	// $actions = array of actions
	public static function pull_actions($actions, $objectID) {
		if (!is_array($actions) || empty($actions)) {
			return [];
		}
	
		$blame = [];
		$db = mfcs::$engine->openDB; // Assuming $db is your database connection object
	
		$actionList = "'" . implode("','", array_map([$db, 'escape'], $actions)) . "'";
		$sql = "SELECT `username`, `date` FROM `logs` WHERE `objectID`='{$db->escape($objectID)}' AND `action` IN ({$actionList})";
		$sqlResult = $db->query($sql);
	
		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__ . "() - " . $sqlResult['error'], errorHandle::DEBUG);
			return [];
		}
	
		while ($row = mysql_fetch_array($sqlResult['result'], MYSQL_ASSOC)) {
			$blame[] = [$row['username'], date('D, d M Y H:i', $row['date'])];
		}
	
		return $blame;
	}

}

?>