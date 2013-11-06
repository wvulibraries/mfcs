<?php

// For this to work, we need the original data structure, not objectdata table

include("../../header.php");

$objects = objects::get();
foreach ($objects as $object) {

	// print "<pre>";
	// var_dump($object);
	// print "</pre>";

	// $return = objects::insertObjectData($object['ID'],$object['data']);

	// print "<pre>";
	// var_dump($return);
	// print "</pre>";

	// exit;

	if (objects::insertObjectData($object['ID'],$object['data']) === FALSE) {
		print "Error.";
		exit;
	}

}

print "Done.";

?>