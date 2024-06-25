<?php

session_save_path('/tmp');
ini_set('memory_limit',-1);
set_time_limit(0);

require("../header.php");

// if (!isCLI()) {
// 	print "Must be run from the command line.";
// 	exit;
// }

// Turn off EngineAPI template engine
// $engine->obCallback = FALSE;


// # Count how many items we need to iterate through.  
// $sqlCount = sprintf("SELECT COUNT(*) AS `processing` FROM `objectProcessing` WHERE `state`='1' ");
// $countQuery = mfcs::$engine->openDB->query($sqlCount); 
// $result = mysqli_fetch_array($countQuery['result'],  MYSQLI_ASSOC);

// # set the count variable as int
// $count = (int) $result['processing'];

// while($count > 0) {
// 	# grab one at a time that is a valid state 
// 	$sql = sprintf("SELECT * FROM `objectProcessing` WHERE `state`='1' LIMIT 1");
// 	$sqlResult = mfcs::$engine->openDB->query($sql);

// 	# if there is nothing break it. 
// 	if (!$sqlResult['result']) {
// 		errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
// 		break;
// 	}


// 	if ($sqlResult['numrows'] == "0") break;

// 	$row = mysqli_fetch_array($sqlResult['result'],  MYSQLI_ASSOC);
// 	files::process($row['objectID'],$row['fieldName']);
// 	$count--; 
// }

// files::deleteOldProcessingJobs();
// files::errorOldProcessingJobs();

?>