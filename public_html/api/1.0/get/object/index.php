<?php
include("../../../../header.php");


// we don't need engine's display handling here. 
$engine->obCallback = FALSE;

try {

	if (!objects::validID(TRUE,$engine->cleanGet['MYSQL']['id'])) {
		throw new Exception("Invalid Object ID.");
	}

	if (($object = objects::get($engine->cleanGet['MYSQL']['id'])) === FALSE) {
		throw new Exception("error getting object.");
	}

	print json_encode($object);

	exit;
}
catch(Exception $e) {

	print json_encode(array("error" => "true", "message" => $e->getMessage()));

}

exit;

?>
	