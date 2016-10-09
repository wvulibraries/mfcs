<?php
include("../../../../header.php");


// we don't need engine's display handling here.
$engine->obCallback = FALSE;

try {

	if (($form = forms::get($engine->cleanGet['MYSQL']['id'])) === FALSE) {
		throw new Exception("error getting form.");
	}

	$json = array();
	$object_count = forms::countInForm($engine->cleanGet['MYSQL']['id']);
	$json[] = array("ID" => $form['ID'], "title" => $form['title'], "displayTitle" => $form['displayTitle'], "description" => $form['description'],"object_count"=>$object_count);

	$json = json_encode($json);
	print (isset($engine->cleanGet['HTML']['prettyPrint']))?json_format($json):$json;

	exit;
}
catch(Exception $e) {

	print json_encode(array("error" => "true", "message" => $e->getMessage()));

}

exit;

?>
