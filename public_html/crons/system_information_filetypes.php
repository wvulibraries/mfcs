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

$sql       = sprintf("SELECT `ID` FROM `objects`");
$sqlResult = $engine->openDB->query($sql);

if (!$sqlResult['result']) {
	errorHandle::newError(__METHOD__."() - : ".$sqlResult['error'], errorHandle::DEBUG);
	return FALSE;
}

$file_types = array("types" => array(), "forms" => array());
while ($row = mysql_fetch_array($sqlResult['result'],  MYSQL_ASSOC)) {

	$objects = objects::get($row['ID'],TRUE);
	getFiles($objects);

}

$sql       = sprintf("UPDATE `system_information` set `value`='%s' WHERE `name`='file_types'",
	encodeFields($file_types)
	);
$sqlResult = $engine->openDB->query($sql);

if (!$sqlResult['result']) {
	var_dump($sqlResult["error"]);
	exit;
}

exit;

#### ###################################################################### ####

function getFiles($object) {

	if ($object['metadata'] == 1) return 0;

	global $file_types;

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

					$ext = pathinfo($file['name'], PATHINFO_EXTENSION);

					$file_types["types"][$ext] = (isset($file_types["types"][$ext]))?$file_types["types"][$ext]+1:1;

					if (!isset($file_types["forms"][$object['formID']]))          $file_types["forms"][$object['formID']]          = array();
					if (!isset($file_types["forms"][$object['formID']]["types"])) $file_types["forms"][$object['formID']]["types"] = array();
					$file_types["forms"][$object['formID']]["types"][$ext] = (isset($file_types["forms"][$object['formID']]["types"][$ext]))?$file_types["forms"][$object['formID']]["types"][$ext]+1:1;

				}

			}
		}
	}

}



?>