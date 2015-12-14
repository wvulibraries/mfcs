<?php
include("../../../../header.php");


try {

	if (($projects = projects::get()) === FALSE) {
		throw new Exception("error getting forms.");
	}

	$json = array();
	foreach ($projects as $project) {
		$json[] = array("ID" => $project['ID'], "title" => $project['projectName'], "shortTitle" => $project['projectID']);
	}

	print json_encode($json);

	exit;
}
catch(Exception $e) {

	print json_encode(array("error" => "true", "message" => $e->getMessage()));

}

exit;

?>
	