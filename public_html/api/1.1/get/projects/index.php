<?php
include("../../../../header.php");


// we don't need engine's display handling here.
$engine->obCallback = FALSE;

try {

	if (($projects = projects::get()) === FALSE) {
		throw new Exception("error getting forms.");
	}

	$projects_array = array();
	foreach ($projects as $project) {
		$projects_array[] = array("ID" => $project['ID'], "title" => $project['projectName'], "shortTitle" => $project['projectID']);
	}

	$json = json_encode($projects_array);
	print (isset($engine->cleanGet['HTML']['prettyPrint']))?json_format($json):$json;

	exit;
}
catch(Exception $e) {

	print json_encode(array("error" => "true", "message" => $e->getMessage()));

}

exit;

?>
