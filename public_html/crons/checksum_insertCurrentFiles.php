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

$forms = forms::getForms(TRUE);

$count = 0;
foreach ($forms as $form) {
	$objects = objects::getAllObjectsForForm($form['ID']);
	$count  += getFiles($form,$objects);
}

print "$count\n";

#### ###################################################################### ####

function getFiles($form,$objects) {

	$count = 0;
	foreach ($objects as $object) {

		if ($object['metadata'] == 1) continue;

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
					}

				}
			}
		}


	}

	return $count;
}



?>