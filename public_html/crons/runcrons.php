<?php

// this file should be scheduled to run every 5 minutes.

session_save_path('/tmp');
ini_set('memory_limit',-1);
set_time_limit(0);

require("../public_html/header.php");

$cronsPath = '/vagrant/public_html/crons';

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

if ($sqlResult['numrows'] == "0") die("Nothing to do.\n");

while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {

  $runjob = false;

  // verify scheduled job exists in the $cronsPath
	if (!file_exists($cronsPath . '/'  . $row['name']) && !$row['active'] == '1') {
    notification::notifyAdmins("MFCS Scheduler Run Failure", "Scheduled Cron Job is missing");
	}
  else{
    if ($row['runnow'] == '1') {
      $runjob = true;
    }
    else {
      $minuteset = ( ($row['minute'] == date("i")) || ($row['minute'] == '*') );
      $hourset = (($row['hour'] == date("G")) || ($row['hour'] == '*'));
      $dayofmonthset = (($row['dayofmonth'] == date("n")) || ($row['dayofmonth'] == '*'));
      $monthset = (($row['month'] == date("n")) || ($row['month'] == '*'));
      $dayofweekset = (($row['dayofweek'] == date("w")) || ($row['dayofweek'] == '*'));

      if ($minuteset && $hourset && $dayofmonthset && $monthset && $dayofweekset) {
        $runjob = true;
      }
    }
  }

  if ($runjob && file_exists($cronsPath . '/'  . $row['name'])) {

    include($cronsPath . '/'  . $row['name']);

    // update last run
		$sql       = sprintf("UPDATE `scheduler` set `runnow`='%s', `lastrun`='%s' WHERE `ID`='%s' LIMIT 1",
      0,
			time(),
			$row['ID']
			);
		$sqlResult_insert = $engine->openDB->query($sql);

		if (!$sqlResult_insert['result']) {
			notification::notifyAdmins("MFCS Database Update Failure", "Failed to set checksum pass check to 1");
		}
  }

}

?>
