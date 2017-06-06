<?php

// this file should be scheduled to run every 5 minutes.

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

$sql       = sprintf("SELECT * FROM `scheduler` WHERE `active` = 1 ");
$sqlResult = mfcs::$engine->openDB->query($sql);

if (!$sqlResult['result']) {
	errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
	return FALSE;
}

if ($sqlResult['numrows'] == "0") die($today . " Nothing to do.\n");

while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {

  $runjob = false;

  // verify scheduled job exists
	if (!file_exists($row['name'])) {
    notification::notifyAdmins("MFCS Scheduler Run Failure", $row['name'] , "Scheduled Cron Job is missing");
	}
  elseif ($row['active'] == '1') {
    if ($row['runnow'] == '1') {
      $runjob = true;
    }
    else {
      $minuteset = ( ($row['minute'] == date("i")) || ($row['minute'] == '*') );
      $hourset = ( ($row['hour'] == date("G")) || ($row['hour'] == '*') );
      $dayofmonthset = ( ($row['dayofmonth'] == date("n")) || ($row['dayofmonth'] == '*') );
      $monthset = ( ($row['month'] == date("n")) || ($row['month'] == '*') );
      $dayofweekset = ( ($row['dayofweek'] == date("w")) || ($row['dayofweek'] == '*') );
      $runjob = ($minuteset && $hourset && $dayofmonthset && $monthset && $dayofweekset);
    }
  }

  if ($runjob) {
    shell_exec("/usr/bin/php " . $row['name']);

    // update last run
		$sql       = sprintf("UPDATE `scheduler` set `runnow`='%s', `lastrun`='%s' WHERE `ID`='%s' LIMIT 1",
      0,
			time(),
			$row['ID']
			);
		$sqlResult_insert = $engine->openDB->query($sql);

		if (!$sqlResult_insert['result']) {
			notification::notifyAdmins("MFCS Database Update Failure", "Failed to set runnow to 0 and lastrun to current time", $row['name']);
		}
  }

}

?>
