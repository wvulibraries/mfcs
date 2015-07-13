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

$forms       = forms::getForms(NULL);

$dupeConfirm = array(TRUE=>0,FALSE=>0);

foreach ($forms as $form) {
	
	print "Form: ".$form['title']."\n";

	$objects = objects::getAllObjectsForForm(1);

	foreach ($objects as $object) {

		unset(mfcs::$engine->cleanPost['MYSQL']);

		$return  = duplicates::updateDupeTable("1",$object['ID'],$object['data']);
		$dupeConfirm[$return]++;

	}

	break;
}

print "\n\n";
var_dump($dupeConfirm);

print "Done.\n\n";

?>