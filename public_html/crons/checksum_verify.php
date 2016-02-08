<?php

session_save_path('/tmp');
ini_set('memory_limit',-1);
set_time_limit(0);

require("../header.php");

if (!isCLI()) {
	print "Must be run from the command line.";
	exit;
}

// Turn off EngineAPI template engine
$engine->obCallback = FALSE;

$sql       = sprintf("SELECT * FROM `filesChecks` WHERE `lastChecked` IS NOT NULL");
$sqlResult = mfcs::$engine->openDB->query($sql);

if (!$sqlResult['result']) {
	errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
	return FALSE;
}

$fileMissingCount = 0;

while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {

	// if there are more than 50 missing files, break out of the loop. 
	// we assume that there is something wrong with the file system at this
	// point. 
	if ($fileMissingCount > 50) break;

	// the full path of the file that we are checking
	$filePath = mfcs::config('archivalPathMFCS')."/".$row['location'];

	// If the file cannot be found
	if (!file_exists($filePath)) {
		$fileMissingCount++;

		notification::notifyAdmins("File missing", $filePath);
		continue;
	}

	$checksum = md5_file($filePath);

	// if the checksum doesn't match up
	if ($checksum != $row['checksum']) {
		// set pass = 0
		
		$sql       = sprintf("UPDATE `filesChecks` set `pass`='0' WHERE `ID`='%s' LIMIT 1",
			$row['ID']
			);
		$sqlResult_insert = $engine->openDB->query($sql);

		if (!$sqlResult_insert['result']) {
			notification::notifyAdmins("MFCS Database Update Failure", "Failed to set checksum pass check to 0");
		}

		notification::notifyAdmins("Checksum failure", $filePath);
		log::insert("fixity",$row['ID'],0,$filePath);

	}

}

?>