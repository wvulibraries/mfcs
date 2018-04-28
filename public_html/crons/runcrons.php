<?php

// this file should be scheduled to run every 5 minutes.

session_save_path('/tmp');
ini_set('memory_limit',-1);
set_time_limit(0);

require("../header.php");
include("../includes/classes/scheduler.php");

$scheduler = new scheduler(".");

if (!isCLI()) {
	print "Must be run from the command line.";
	exit;
}

// Turn off EngineAPI template engine
$engine->obCallback = FALSE;

$sql       = sprintf("SELECT * FROM `scheduler` WHERE `active` = 1 ");
$sqlResult = $engine->openDB->query($sql);

if (!$sqlResult['result']) {
	errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
	return FALSE;
}

if ($sqlResult['numrows'] == "0") die(date("Y-m-d h:i:sa") . " Nothing to do.\n");

while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {

  // verify scheduled job exists
	if (!file_exists($row['name'])) {
    notification::notifyAdmins("MFCS Scheduler Run Failure", $row['name'] , "Scheduled Cron Job is missing");
	}
  elseif ($row['active'] == '1') {
    if ($row['runnow'] == '1') {
      $scheduler->runjob($row);
			print date("Y-m-d h:i:sa") . " Job " . $row['name'] . "\n";
    }
    elseif ($scheduler->timetorun($row)) {
			$scheduler->runjob($row);
			print date("Y-m-d h:i:sa") . " Job " . $row['name'] . "\n";
    }
  }

}

?>
