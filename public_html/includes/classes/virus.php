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

		$sql       = sprintf("INSERT INTO `virusChecks` (`objectID`,`fieldName`,`state`, `timestamp`) VALUES('%s','%s','1','%s')",
			mfcs::$engine->openDB->escape($objectID),
			mfcs::$engine->openDB->escape($field_name),
			time()
			);
		$sqlResult = mfcs::$engine->openDB->query($sql);

		if (!$sqlResult['result']) {

			mfcs::$engine->openDB->transRollback();
			mfcs::$engine->openDB->transEnd();

			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}
		

		// end transactions
		mfcs::$engine->openDB->transCommit();
		mfcs::$engine->openDB->transEnd();

		return TRUE;

	}

	public static function clear_table() {

		$sql       = sprintf("DELETE FROM `virusChecks` WHERE `state`='0'");
		$sqlResult = mfcs::$engine->openDB->query($sql);
		
		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}
		
		return TRUE;

	}

	private static function scan_file($virus_id, $filename) {

		if (!file_exists($filename)) {
			errorHandle::newError(__METHOD__."() - File does not exist!", errorHandle::DEBUG);
			return FALSE;
		}

		$filename     = escapeshellarg($filename);
		$command      = sprintf("%s %s",mfcs::config('virus_scan_cmd'), $filename);
		
		$return_value = -1;
		$cmd_output   = "";

		exec($command, $cmd_output, $return_value);

		// use === here, because if the command doesn't exist, 0 evaluates to 
		// a success
		if ($return_value === 0) {
			return TRUE;
		}

		return FALSE;

	}

	// 0 = done
	// 1 = needs processing
	// 2 = currently working on
	// 3 = virus found
	public static function set_virus_state($virus_id,$state) {

		$sql       = sprintf("UPDATE `virusChecks` SET `state`='%s' WHERE `ID`='%s'",
			mfcs::$engine->openDB->escape($state),
			mfcs::$engine->openDB->escape($virus_id)
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
		$sqlResult = mfcs::$engine->openDB->query($sql);
		
		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}
		
		// check to make sure the scanning command is available, before we 
		// loop through the files. 
		$check_cmd = mfcs::config('virus_scan_cmd_check');
		if (!`which $check_cmd`) {
			checks::set_error("virus_cmd");
			return FALSE;
		}

		while ($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {

			// set the state of the row to 2, the "working" state
			self::set_virus_state($row['ID'],"2"); 

			// get the object, and ignore the cache since we are updating in a loop
			$object   = objects::get($row['objectID'],TRUE);
			$files    = $object['data'][$row['fieldName']];
			$assetsID = $files['uuid'];

			foreach ($files['files']['archive'] as $file) {

				if (self::scan_file($row['ID'],sprintf("%s/%s/%s",mfcs::config('archivalPathMFCS'),$file['path'],$file['name'])) === FALSE) {
					// Virus Found
					self::set_virus_state($row['ID'],'3');

				}
				else {
					// clean
					self::set_virus_state($row['ID'],'0');
				}

			}


		}

		return TRUE;

	}

	public static function notify_of_virus() {

		$sql       = sprintf("SELECT * FROM `virusChecks` WHERE `state`='3'");
		$sqlResult = mfcs::$engine->openDB->query($sql);
		
		if (!$sqlResult['result']) {
			errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
			return FALSE;
		}
		
		while ($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {
			notification::notifyAdmins("Virus Found.", "ObjectID: ".$row['objectID']);
		}

	}

}

?>