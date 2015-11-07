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

$sql       = sprintf("SELECT COUNT(*) FROM `objects`");
$sqlResult = $engine->openDB->query($sql);

if (!$sqlResult['result']) {
	errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
	return FALSE;
}

$row          = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC);
$totalObjects = $row["COUNT(*)"];
printf("Total Objects: %s\n",$totalObjects);

$sql       = sprintf("SELECT `ID` FROM `objects`");
$sqlResult = $engine->openDB->query($sql);

if (!$sqlResult['result']) {
	errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
	return FALSE;
}

$count = 0;
while ($row       = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {

	$objects = objects::get($row['ID'],TRUE);
	$count  += getFiles($objects);

}


// for ($I=1;$I<= $totalObjects;$I += 10000) {
	// $objects = objects::getObjects(1,80000);
	// $count  += getFiles($objects);
	// printf("Current Count: %s\n",$count);
// }

print "$count\n";

#### ###################################################################### ####

function getFiles($object) {

	$count = 0;
	// foreach ($objects as $object) {

		if ($object['metadata'] == 1) return 0;

		$form = forms::get($object['formID']);

		foreach ($form['fields'] as $field) {
			if ($field['type'] == "file") {
				if (isset($object['data'][$field['name']])                        && 
					isset($object['data'][$field['name']]['files'])               && 
					isset($object['data'][$field['name']]['files']['archive'])    && 
					is_array($object['data'][$field['name']]['files']['archive']) && 
					count($object['data'][$field['name']]['files']['archive']) > 0) 
				{

					foreach ($object['data'][$field['name']]['files']['archive'] as $file) {
						$count++;
						$location = sprintf("%s/%s",
							$file['path'],
							$file['name']
							);

						$sql       = sprintf("INSERT INTO `filesChecks` (`location`) VALUES('%s')",
							mfcs::$engine->openDB->escape($location)
							);
						$sqlResult = mfcs::$engine->openDB->query($sql);

						if (!$sqlResult['result']) {
							errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
							return FALSE;
						}

					}

				}
			}
		}


	// }

	return $count;
}



?>