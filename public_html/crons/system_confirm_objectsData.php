<?php

session_save_path('/tmp');
ini_set('memory_limit',-1);
set_time_limit(0);

require("../header.php");

if (!isCLI()) {
    print "Must be run from the command line.";
    exit;
}

$sql       = sprintf("SELECT `objectID` FROM `objectsData` WHERE `objectID` NOT IN ( SELECT `objects`.`ID` FROM `objects` );");
$sqlResult = mfcs::$engine->openDB->query($sql);

if (!$sqlResult['result']) {
    errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
    die;
}

while($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {

    $sql       = sprintf("DELETE FROM `objectsData` WHERE `objectID`='%s'",
        $engine->openDB->escape($row['objectID'])
        );
    $sqlResult2 = mfcs::$engine->openDB->query($sql);

    if (!$sqlResult2['result']) {
        errorHandle::newError(__METHOD__."() - : ".$sqlResult2['error'], errorHandle::DEBUG);
        die;
    }

}

?>