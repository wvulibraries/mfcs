<?php
	session_save_path('/tmp');

	include "../public_html/header.php";

	$form_id = "33";

	$objects = objects::getAllObjectsForForm($form_id);

	// count unmber of returned objects
	$object_count = count($objects);

	$non_public = 0;
	$public = 0;
	// Set Table name
	foreach ($objects as $object) {
		if ($object['publicRelease'] == "0") continue;
	    $public++;
	}

	$non_public = $object_count - $public;

	print "Total objects: " . $object_count . "\n";
	print "public objects: " . $public . "\n";
	print "Non-public objects: " . $non_public . "\n";
?>