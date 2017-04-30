<?php
include("../../../../header.php");

// we don't need engine's display handling here.
$engine->obCallback = FALSE;
http::setContentType("application/json");

try {

  if (is_empty($engine->cleanGet['MYSQL']['id']) || is_empty($engine->cleanGet['MYSQL']['url'])) {
    throw new Exception("Must provide both 'id' and 'url' parameters.");
  }
	if (($object = objects::getByIDNO($engine->cleanGet['MYSQL']['id'])) === FALSE) {
		throw new Exception("Error getting object.");
	}
  if (objects::setUrl($object['ID'],urldecode($engine->cleanGet['MYSQL']['url'])) === FALSE) {
    throw new Exception("Error setting URL.");
  }

	$json = json_encode(array("error" => "false", "message" => sprintf("successfully added/updated URL in database for Object: %s",$engine->cleanGet['MYSQL']['id'])));
	print (isset($engine->cleanGet['HTML']['prettyPrint']))?json_format($json):$json;

	exit;
}
catch(Exception $e) {

	print json_encode(array("error" => "true", "message" => $e->getMessage()));

}

exit;

?>
