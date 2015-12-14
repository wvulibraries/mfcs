<?php
include("../../../../header.php");


try {

	if (($forms = forms::getForms(TRUE,TRUE)) === FALSE) {
		throw new Exception("error getting forms.");
	}

	$json = array();
	foreach ($forms as $form) {
		$json[] = array("ID" => $form['ID'], "title" => $form['title'], "displayTitle" => $form['displayTitle'], "description" => $form['description']);
	}

	print json_encode($json);

	exit;
}
catch(Exception $e) {

	print json_encode(array("error" => "true"));


}

exit;

?>
	