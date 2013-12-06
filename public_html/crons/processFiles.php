<?php

session_save_path('/tmp');

require("../header.php");

$count = 0;
while (FALSE) {

	$sql       = sprintf("SELECT * FROM `objectProcessing` WHERE `state`='1' LIMIT 1");
	$sqlResult = mfcs::$engine->openDB->query($sql);

	if (!$sqlResult['result']) {
		errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
		// we break here, instead of continue so that we don't get stuck in an infinate loop.
		break;
	}

	if ($sqlResult['numrows'] == "0") break;

	$row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);

	files::process($row['objectID'],$row['fieldName']);
	
	// safety. we don't have any projects that are more than 50000 items currently
	// @TODO pull this dynamically from the system. get a best guess max processing
	if ($count++ > 50000) break;

}

files::errorOldProcessingJobs();
files::deleteOldProcessingJobs();

?>