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
		$objects_count = forms::countInForm($form['ID']);
		// TODO we need to include a last modified date here
		$json[] = array("ID" => $form['ID'], "title" => $form['title'], "displayTitle" => $form['displayTitle'], "description" => $form['description'],"object_count"=>$objects_count);
	}

	$json = json_encode($json);
	print (isset($engine->cleanGet['HTML']['prettyPrint']))?json_format($json):$json;

	exit;
}
catch(Exception $e) {

	print json_encode(array("error" => "true", "message" => $e->getMessage()));

}

exit;

?>
