<?php

class scheduler {

  private $availablecrons = array();

  public function __construct($dir) {

    if (!is_readable($dir)) {
      return FALSE;
    }

    $dir = opendir($dir); // open the cwd..also do an err check.
    while(false != ($file = readdir($dir))) {
      if(($file != ".") && ($file != "..") && ($file != "runcrons.php")) {
        $this->availablecrons[] = array('value' => $file, 'label' => str_replace('_',' ',basename($file,'.php')));
      }
    }

    closedir($dir);

  }

  public function getCronsArray() {
    return $this->availablecrons;
  }

  public function createSelect($start, $end) {
    $selectarray = array();
    $selectarray[] = array('value' => '*', 'label' => '*', 'selected' => TRUE);
    for ($currentpos = $start; $currentpos <= $end; $currentpos++) {
      $selectarray[] = array('value' => (string) $currentpos, 'label' => (string) $currentpos);
    }

    return $selectarray;

  }

  public function minuteSelect() {
    $minutearray = array();
    $minutearray[] = array('value' => '*', 'label' => '*', 'selected' => TRUE);
    $minutearray[] = array('value' => '0', 'label' => '0');
    $minutearray[] = array('value' => '15', 'label' => '15');
    $minutearray[] = array('value' => '30', 'label' => '30');
    $minutearray[] = array('value' => '45', 'label' => '45');

    return $minutearray;

  }

  public function monthSelect() {
    $month_names = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
    $montharray = array();
    $montharray[] = array('value' => '*', 'label' => '*', 'selected' => TRUE);
    for ($currentpos = 1; $currentpos <= 12; $currentpos++) {
      $montharray[] = array('value' => (string) $currentpos, 'label' => $month_names[$currentpos-1]);
  	}

    return $montharray;

  }

  public function weekdaySelect() {
    $weekday_names = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
    $daysarray = array();
    $daysarray[] = array('value' => '*', 'label' => '*', 'selected' => TRUE);
    for ($currentpos = 0; $currentpos <= 6; $currentpos++) {
        $daysarray[] = array('value' => (string) $currentpos, 'label' => $weekday_names[$currentpos]);
    }

    return $daysarray;

  }

  public function timetorun($job){
    $minuteset = ( ($job['minute'] == date("i")) || ($job['minute'] == '*') );
    $hourset = ( ($job['hour'] == date("G")) || ($job['hour'] == '*') );
    $dayofmonthset = ( ($job['dayofmonth'] == date("n")) || ($job['dayofmonth'] == '*') );
    $monthset = ( ($job['month'] == date("n")) || ($job['month'] == '*') );
    $dayofweekset = ( ($job['dayofweek'] == date("w")) || ($job['dayofweek'] == '*') );
    return ($minuteset && $hourset && $dayofmonthset && $monthset && $dayofweekset);
  }

  public function runjob($job){
    shell_exec("/usr/bin/php " . $job['name']);

    // update last run
    $sql       = sprintf("UPDATE `scheduler` set `runnow`='%s', `lastrun`='%s' WHERE `ID`='%s' LIMIT 1",
      0,
      time(),
      $job['ID']
      );
    $sqlResult_insert = mfcs::$engine->openDB->query($sql);

    if (!$sqlResult_insert['result']) {
      notification::notifyAdmins("MFCS Database Update Failure", "Failed to set runnow to 0 and lastrun to current time", $job['name']);
    }
  }

}

?>
