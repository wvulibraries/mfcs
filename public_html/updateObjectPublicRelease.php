<?php

session_save_path('/tmp');
ini_set('memory_limit',-1);
set_time_limit(0);

require("../public_html/header.php");

$engine->obCallback = FALSE;
ob_end_clean();

if (!isCLI()) {
	print "Must be run from the command line.";
	exit;
}

if (mfcs::$engine->openDB->transBegin("objects") !== TRUE) {
    print "Unable to start database transactions";
    exit;
}

$objects_temp = array();

$object_count = objects::countObjects(false);
$object_count_check = 0;
for($offset = 0; $offset < ceil($object_count/100); $offset++) {
	foreach (objects::getObjects($offset*100, 100, false, true) as $object) {
		$objects_temp[$object['ID']] = 1;
        if (isset($object['data']['publicRelease']) && !is_empty($object['data']['publicRelease']) && strtolower($object['data']['publicRelease']) == "no") {
            $public_release = 0;
        }
        else {
            $public_release = 1;
        }

        // Note: we don't need to escape the inputs here because the data is known and clean.
        $sql       = sprintf("UPDATE `objects` SET `publicRelease`='%s' WHERE `ID`='%s' LIMIT 1",
                             $public_release,
                             $object['ID']);
        $sqlResult = mfcs::$engine->openDB->query($sql);

        if (!$sqlResult['result']) {
            print "Error updating database: ".$sqlResult['error']."\n";
			$engine->openDB->transRollback();
			$engine->openDB->transEnd();
            exit;
        }

	}
	unset($objects);
}

if ($object_count != count($objects_temp)) {
    $engine->openDB->transRollback();
    $engine->openDB->transEnd();
	printf("Safety check failed. '%s' != '%s' Rolling back trasaction.\n", $object_count, $object_count_check);
    exit;
}

mfcs::$engine->openDB->transCommit();
mfcs::$engine->openDB->transEnd();

print "Done.\n";
?>
