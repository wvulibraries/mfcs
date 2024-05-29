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

$sql       = sprintf("SELECT * FROM `objectProcessing` WHERE `state`='1' AND `objectID`='%s' LIMIT 1",
$engine->openDB->escape($argv[1])
);

$sqlResult = mfcs::$engine->openDB->query($sql);

if (!$sqlResult['result']) {
	errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
	// we break here, instead of continue so that we don't get stuck in an infinate loop.
	die("mysql broke.\n");
}

if ($sqlResult['numrows'] == "0") die("Nothing to do.\n");

$row = mysqli_fetch_array($sqlResult['result']);

files::process($row['objectID'],$row['fieldName']);

files::deleteOldProcessingJobs();
files::errorOldProcessingJobs();

print "\nDone.\n";

?>
