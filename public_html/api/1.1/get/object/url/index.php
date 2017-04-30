<?php
include("../../../../../header.php");
// TODO this should be included in general object retrievial ... but I don't want
// to change up how that works, without better unit testing in place.
// Having this included in general object retrievial doesn't remove the need
// for this endpoint.

// we don't need engine's display handling here.
$engine->obCallback = FALSE;

try {
	if (!objects::validID(TRUE,$engine->cleanGet['MYSQL']['id'])) throw new Exception("Invalid Object ID.");
	if (($object_url = objects::getUrl($engine->cleanGet['MYSQL']['id'])) === FALSE) throw new Exception("error getting object.");
	$json = sprintf('{"url": "%s"}', $object_url);
	print (isset($engine->cleanGet['HTML']['prettyPrint']))?json_format($json):$json;
	exit;
}
catch(Exception $e) {
	print json_encode(array("error" => "true", "message" => $e->getMessage()));
}
exit;
?>
