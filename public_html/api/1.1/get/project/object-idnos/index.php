<?php
include("../../../../header.php");

ini_set('memory_limit',-1);
set_time_limit(0);

// we don't need engine's display handling here.
$engine->obCallback = FALSE;

try {

	if (!projects::validID($engine->cleanGet['MYSQL']['id'])) throw new Exception("Invalid Project ID.");
  if (($project_idnos = projects::get_project_idnos($engine->cleanGet['MYSQL']['id'])) === false) {
    throw new Exception("Unable to retrieve IDNOs");
  }

	$json = json_encode($idnos);
	print (isset($engine->cleanGet['HTML']['prettyPrint']))?json_format($json):$json;

	exit;
}
catch(Exception $e) {
	print json_encode(array("error" => "true", "message" => $e->getMessage()));
}

exit;
?>
