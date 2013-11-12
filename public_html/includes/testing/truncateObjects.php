<?php

include("../../header.php");


$sql       = array();
$sql[] = "TRUNCATE TABLE `dupeMatching`";
$sql[] = "TRUNCATE TABLE `objectMetadataLinks`";
$sql[] = "TRUNCATE TABLE `objectProjects`";
$sql[] = "TRUNCATE TABLE `objects`";

foreach ($sql as $I=>$K) {
	$sqlResult = $engine->openDB->query($K);
	if ($sqlResult['result'] === FALSE) {
		print $K."\n";
		die($sqlResult['error']);
	}
}

print "Done.";

?>