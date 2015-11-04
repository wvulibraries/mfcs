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

$sql       = sprintf("SELECT * FROM `filesChecks` WHERE `lastChecked` IS NULL");
$sqlResult = mfcs::$engine->openDB->query($sql);

if (!$sqlResult['result']) {
	errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
	return FALSE;
}

while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {

	$checksum = md5_file(mfcs::config('archivalPathMFCS')."/".$row['location']);

	$sql       = sprintf("UPDATE `filesChecks` set `checksum`='%s', lastChecked='%s', pass='1' WHERE ID='%s' LIMIT 1",
		$checksum,
		time(),
		$row['ID']
		);
	$sqlResult_insert = $engine->openDB->query($sql);
	
	if (!$sqlResult_insert['result']) {
		// @todo alert via email? 
	}
	

}

?>