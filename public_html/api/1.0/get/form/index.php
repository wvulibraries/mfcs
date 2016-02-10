<?php
include("../../../../header.php");

ini_set('memory_limit',-1);
set_time_limit(0);

// we don't need engine's display handling here. 
$engine->obCallback = FALSE;

$slice = (isset($engine->cleanGet['MYSQL']['slice']) && validate::integer($engine->cleanGet['MYSQL']['slice']))?$engine->cleanGet['MYSQL']['slice']:1000;

try {

	// ID is always passed into the API as "id" 
	// set it as "formID" that the form class expects
	http::setGet("formID",$engine->cleanGet['MYSQL']['id']);

	if (!forms::validID()) {
		throw new Exception("Invalid Form ID.");
	}

	if (($objects = objects::getAllObjectsForForm($engine->cleanGet['MYSQL']['id'],"idno")) === FALSE) {
		throw new Exception("error getting objects.");
	}

	// we only return 1000? objects at a time. Calling application tells us where to start 
	$objects       = array_slice($objects, $engine->cleanGet['MYSQL']['start'], $slice);
	$objects       = array_map("process_objects", $objects);
	$objects_count = count($objects);
	$objects       = array_filter($objects);

	// we need to make sure there are no empty indexes in the array, otherwise 
	// json_encode doesn't encode it as an array.
	if ($objects_count > count($objects)) {

		$temp = array();
		foreach ($objects as $object) {
			array_push($temp, $object);
		}

		$objects = $temp;

	}

	$json    = json_encode($objects);

	print (isset($engine->cleanGet['HTML']['prettyPrint']))?json_format($json):$json;

	exit;
}
catch(Exception $e) {

	print json_encode(array("error" => "true", "message" => $e->getMessage()));

}

exit;

?>
	