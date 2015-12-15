<?php
include("../../../../header.php");


// we don't need engine's display handling here. 
$engine->obCallback = FALSE;

try {

	if (($forms = forms::getForms(TRUE,TRUE)) === FALSE) {
		throw new Exception("error getting forms.");
	}

	$json = array();
	foreach ($forms as $form) {
		$object_count = count(objects::getAllObjectsForForm($form['ID']));
		$json[] = array("ID" => $form['ID'], "title" => $form['title'], "displayTitle" => $form['displayTitle'], "description" => $form['description'],"object_count"=>$object_count);
	}

	print json_encode($json);

	exit;
}
catch(Exception $e) {

	print json_encode(array("error" => "true", "message" => $e->getMessage()));

}

exit;

?>
	